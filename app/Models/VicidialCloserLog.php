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
     * Vicidial's placeholder user for inbound calls that no agent ever took.
     * Not a real agent, so it is offered as its own filter option.
     */
    public const NO_AGENT_USER = 'VDCL';

    /**
     * Limit the query to one agent. Vicidial stores the no-agent placeholder
     * in the same column, so NO_AGENT_USER works here too.
     */
    public function scopeForAgent($query, string $user)
    {
        return $query->where('user', $user);
    }

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
     * Whether this call was never picked up by a real agent.
     */
    public function getHasNoAgentAttribute(): bool
    {
        return $this->user === self::NO_AGENT_USER;
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

    /**
     * Get the list the call belongs to.
     */
    public function vicidialList()
    {
        return $this->belongsTo(VicidialList::class, 'list_id', 'list_id');
    }

    /**
     * Get the agent that handled the call.
     */
    public function vicidialUser()
    {
        return $this->belongsTo(VicidialUser::class, 'user', 'user');
    }
}
