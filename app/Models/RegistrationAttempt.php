<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationAttempt extends Model
{
    protected $fillable = [
        'email_hash',
        'ip_hash',
        'fingerprint_hash',
        'phone_hash',
        'status',
    ];
}
