<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Permissions live on the shared UPS auth database (not per-tenant company DB).
 */
class Permission extends SpatiePermission
{
    protected $connection = 'ups';
}
