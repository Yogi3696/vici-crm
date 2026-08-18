<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicidialStatus extends Model
{
    use HasFactory;

    protected $connection = 'asterisk';

    protected $table = 'vicidial_statuses';
    protected $primaryKey = 'status';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
}
