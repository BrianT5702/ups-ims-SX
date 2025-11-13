<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaseModel extends Model
{
    protected $connection = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Use the current database connection from session
        $sessionDb = session('active_db');
        if ($sessionDb && array_key_exists($sessionDb, config('database.connections'))) {
            $this->connection = $sessionDb;
        } else {
            $this->connection = DB::getDefaultConnection();
        }
    }

    public function getConnectionName()
    {
        return $this->connection;
    }
}
