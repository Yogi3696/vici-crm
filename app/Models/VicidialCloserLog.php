<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicidialCloserLog extends Model
{
    use HasFactory;

    protected $connection = 'asterisk';

    protected $table = 'vicidial_closer_log';
    protected $primaryKey = 'closecallid';
    public $timestamps = false;
    
    protected $guarded = [];

    /**
     * Get the status associated with the closer log.
     */
    public function vicidialStatus()
    {
        return $this->belongsTo(VicidialStatus::class, 'status', 'status');
    }
}
