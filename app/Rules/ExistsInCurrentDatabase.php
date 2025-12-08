<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ExistsInCurrentDatabase implements ValidationRule
{
    protected $table;
    protected $column;
    protected $connection;

    public function __construct($table, $column = 'id', $connection = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->connection = $connection;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get the session database first, then fallback to default
        $sessionDb = session('active_db');
        $currentConnection = $this->connection ?: ($sessionDb ?: DB::getDefaultConnection());
        
        // Force the connection to use the session database
        if ($sessionDb && array_key_exists($sessionDb, config('database.connections'))) {
            $exists = DB::connection($sessionDb)
                ->table($this->table)
                ->where($this->column, $value)
                ->exists();
        } else {
            $exists = DB::table($this->table)
                ->where($this->column, $value)
                ->exists();
        }
        
        if (!$exists) {
            $fail('The selected :attribute does not exist.');
        }
    }
}

