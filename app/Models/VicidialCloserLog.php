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
     * Vicidial statuses that mean the inbound call was never handled by an
     * agent. Codes are capped at 6 characters by Vicidial, hence NANQUE.
     */
    public const MISSED_STATUSES = [
        'B',       // Busy
        'N',       // No Answer
        'NA',      // No Answer (alternate code)
        'DROP',    // Agent Not Available
        'NANQUE',  // Inbound No Agent No Queue Drop
        'AFTHRS',  // Inbound After Hours Drop
        'TIMEOT',  // Inbound Queue Timeout Drop
        'WAITTO',  // Inbound Wait Timeout
    ];

    /**
     * Limit the query to calls that were never handled by an agent.
     */
    public function scopeMissed($query)
    {
        return $query->whereIn('status', self::MISSED_STATUSES);
    }

    /**
     * Limit the query to calls that reached an agent.
     */
    public function scopeAnswered($query)
    {
        return $query->whereNotIn('status', self::MISSED_STATUSES);
    }

    /**
     * Whether this call counts as missed.
     */
    public function getIsMissedAttribute(): bool
    {
        return in_array($this->status, self::MISSED_STATUSES, true);
    }

    /**
     * Get the status associated with the closer log.
     */
    public function vicidialStatus()
    {
        return $this->belongsTo(VicidialStatus::class, 'status', 'status');
    }
}
