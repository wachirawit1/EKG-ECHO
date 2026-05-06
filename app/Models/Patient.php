<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $connection = 'mysql';
    protected $table = 'patient';
    protected $primaryKey = 'p_id';
    protected $fillable = [
        'p_id',
        'hn',
        'title_name',
        'fname',
        'lname',
        'hospital_name',
    ];
    
}
