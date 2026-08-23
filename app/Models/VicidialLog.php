<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VicidialLog extends Model
{
    use HasFactory;

    protected $connection = 'asterisk';

    protected $table = 'vicidial_log';

    /**
     * Vicidial keys outbound calls by the Asterisk uniqueid, a string such as
     * "1770200231.82", so it neither increments nor casts to an integer.
     */
    protected $primaryKey = 'uniqueid';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'lead_id' => 'integer',
        'list_id' => 'integer',
        'call_date' => 'datetime',
        'start_epoch' => 'integer',
        'end_epoch' => 'integer',
        'length_in_sec' => 'integer',
        'called_count' => 'integer',
    ];

    /**
     * Statuses that mean the outbound attempt never reached a live person.
     * Names come from this install's vicidial_statuses table; codes are capped
     * at 6 characters by Vicidial. Dispositions such as NI, DNC and DEC are
     * deliberately absent because a human did answer those calls.
     */
    public const NO_CONTACT_STATUSES = [
        'B',      // Busy
        'AB',     // Busy Auto
        'N',      // No Answer
        'NA',     // No Answer AutoDial
        'A',      // Answering Machine
        'AA',     // Answering Machine Auto
        'AM',     // Answering Machine SentToMesg
        'DC',     // Disconnected Number
        'ADC',    // Disconnected Number Auto
        'DAIR',   // Dead Air
        'DROP',   // Agent Not Available
        'PDROP',  // Outbound Pre-Routing Drop
    ];

    /**
     * Term reasons Vicidial records when nobody on our side ended the call.
     * Constrained by the term_reason enum on the table.
     */
    public const ABANDON_TERM_REASONS = [
        'ABANDON',
        'QUEUETIMEOUT',
        'AFTERHOURS',
    ];

    /**
     * Limit the query to one agent.
     */
    public function scopeForAgent($query, string $user)
    {
        return $query->where('user', $user);
    }

    /**
     * Limit the query to one campaign.
     */
    public function scopeForCampaign($query, string $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Limit the query to calls placed within an inclusive date range. Either
     * bound may be omitted.
     */
    public function scopeCallDateBetween($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('call_date', '>=', $from . ' 00:00:00');
        }

        if ($to) {
            $query->where('call_date', '<=', $to . ' 23:59:59');
        }

        return $query;
    }

    /**
     * Limit the query to attempts that never reached a live person.
     */
    public function scopeNoContact($query)
    {
        return $query->whereIn('status', self::NO_CONTACT_STATUSES);
    }

    /**
     * Limit the query to attempts that did reach someone.
     */
    public function scopeContacted($query)
    {
        return $query->whereNotIn('status', self::NO_CONTACT_STATUSES);
    }

    /**
     * Whether this attempt never reached a live person.
     */
    public function getIsNoContactAttribute(): bool
    {
        return in_array($this->status, self::NO_CONTACT_STATUSES, true);
    }

    /**
     * Whether the call was dropped or abandoned rather than ended by a party.
     */
    public function getIsAbandonedAttribute(): bool
    {
        return in_array($this->term_reason, self::ABANDON_TERM_REASONS, true);
    }

    /**
     * Call length as minutes and seconds, e.g. "3:07". Sorting should still
     * target the raw length_in_sec column so the order matches the display.
     */
    public function getLengthInMinutesAttribute(): string
    {
        $seconds = (int) $this->length_in_sec;

        return intdiv($seconds, 60) . ':' . str_pad($seconds % 60, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get the status associated with the log entry.
     */
    public function vicidialStatus(): BelongsTo
    {
        return $this->belongsTo(VicidialStatus::class, 'status', 'status');
    }

    /**
     * Get the list the call belongs to.
     */
    public function vicidialList(): BelongsTo
    {
        return $this->belongsTo(VicidialList::class, 'list_id', 'list_id');
    }

    /**
     * Get the campaign the call belongs to.
     */
    public function vicidialCampaign(): BelongsTo
    {
        return $this->belongsTo(VicidialCampaign::class, 'campaign_id', 'campaign_id');
    }

    /**
     * Get the agent that placed the call.
     */
    public function vicidialUser(): BelongsTo
    {
        return $this->belongsTo(VicidialUser::class, 'user', 'user');
    }

    /**
     * Get the lead the call was placed to.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'lead_id');
    }
}
