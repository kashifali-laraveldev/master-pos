<?php

namespace App\Models;

use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasDomains;

    protected $fillable = [
        'id',
        'name',
        'business_name',
        'email',
        'password',
        'plan',
        'status',
        'trial_ends_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];
}
