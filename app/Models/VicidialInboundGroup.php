<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicidialInboundGroup extends Model
{
    use HasFactory;

    protected $table = 'vicidial_inbound_groups';
    protected $primaryKey = 'group_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
}
