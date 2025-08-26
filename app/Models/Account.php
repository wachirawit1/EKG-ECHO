<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Account extends Authenticatable
{
    protected $connection = 'sqlsrv2';
    protected $table = 'vwUserInfo';
    protected $primaryKey = 'userid'; //กำหนด primary key
    public $timestamps = false;

    protected $fillable = [
        'username',
        'fname',
        'lname',
        'password',
        'department',
        'position'
    ];

    protected $hidden = ['password',];
}
