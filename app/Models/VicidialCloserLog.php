<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicidialCloserLog extends Model
{
    use HasFactory;

    protected $table = 'vicidial_closer_log';
    protected $primaryKey = 'closecallid';
    public $timestamps = false;
    
    protected $guarded = [];
}
