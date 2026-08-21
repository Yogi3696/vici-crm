<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicidialUser extends Model
{
    use HasFactory;

    protected $connection = 'asterisk';

    protected $table = 'vicidial_users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $guarded = [];
}
