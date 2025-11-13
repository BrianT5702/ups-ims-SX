<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueInCurrentDatabase implements ValidationRule
{
    protected $table;
    protected $column;
    protected $ignoreId;
    protected $ignoreColumn;

    public function __construct($table, $column = null, $ignoreId = null, $ignoreColumn = 'id')
    {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->ignoreColumn = $ignoreColumn;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get the session database first, then fallback to default
        $sessionDb = session('active_db');
        $currentConnection = $sessionDb ?: DB::getDefaultConnection();
        
        
        // Force the connection to use the session database
        if ($sessionDb && array_key_exists($sessionDb, config('database.connections'))) {
            $query = DB::connection($sessionDb)->table($this->table)->where($this->column ?? $attribute, $value);
        } else {
            $query = DB::table($this->table)->where($this->column ?? $attribute, $value);
        }
        
        if ($this->ignoreId) {
            $query->where($this->ignoreColumn, '!=', $this->ignoreId);
        }
        
        if ($query->exists()) {
            $fail('The :attribute has already been taken.');
        }
    }
}
