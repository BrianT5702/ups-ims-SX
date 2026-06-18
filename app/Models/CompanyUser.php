<?php

namespace App\Models;

/**
 * User row on the active company database (ups, urs, ups2, etc.).
 * Used for FK relationships on tenant models — not for authentication.
 */
class CompanyUser extends BaseModel
{
    protected $table = 'users';

    protected $guarded = [];
}
