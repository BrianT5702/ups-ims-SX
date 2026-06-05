<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Roles live on the shared UPS auth database (not per-tenant company DB).
 */
class Role extends SpatieRole
{
    protected $connection = 'ups';
}
