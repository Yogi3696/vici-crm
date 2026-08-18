<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    protected $connection = 'asterisk';

    protected $table = 'vicidial_list';
    protected $primaryKey = 'lead_id';

    /**
     * Vicidial keeps its own timestamps: entry_date is set on insert and
     * modify_date is maintained by the column default. The table also carries an
     * updated_at column, but no created_at, so Eloquent's timestamps stay off to
     * avoid writing to a column that does not exist.
     */
    public $timestamps = false;

    protected $guarded = ['lead_id'];

    protected $casts = [
        'lead_id' => 'integer',
        'list_id' => 'integer',
        'entry_list_id' => 'integer',
        'called_count' => 'integer',
        'rank' => 'integer',
        'gmt_offset_now' => 'decimal:2',
        'entry_date' => 'datetime',
        'modify_date' => 'datetime',
        'last_local_call_time' => 'datetime',
        'date_of_birth' => 'date',
    ];

    /**
     * The list this lead belongs to. Campaign membership is reached through it,
     * since vicidial_list has no campaign_id of its own.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(VicidialList::class, 'list_id', 'list_id');
    }

    public function statusDetail(): BelongsTo
    {
        return $this->belongsTo(VicidialStatus::class, 'status', 'status');
    }

    public function getFullNameAttribute(): string
    {
        $name = trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_initial,
            $this->last_name,
        ])));

        return $name !== '' ? $name : '';
    }

    /**
     * Match by prefix rather than substring: a leading wildcard cannot use an
     * index, and the table runs to six figures. Only phone_number is indexed
     * among the searchable columns, so a digits-only term queries it alone and
     * stays a range scan; a text term still scans, as the name and email
     * columns carry no index.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $digits = preg_replace('/\D/', '', $term);

        // A digits-only term is treated as a phone prefix and nothing else:
        // OR-ing in unindexed columns makes MySQL abandon the phone_number
        // index and scan the whole table.
        if ($digits !== '' && $digits === $term) {
            return $query->where('phone_number', 'like', "{$digits}%");
        }

        return $query->where(function ($q) use ($term, $digits) {
            $q->where('first_name', 'like', "{$term}%")
              ->orWhere('last_name', 'like', "{$term}%")
              ->orWhere('email', 'like', "{$term}%")
              ->orWhere('vendor_lead_code', 'like', "{$term}%");

            if ($digits !== '') {
                $q->orWhere('phone_number', 'like', "{$digits}%");
            }
        });
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeForList($query, $listId)
    {
        return $listId ? $query->where('list_id', $listId) : $query;
    }

    /**
     * Leads belonging to any list of the given campaign.
     */
    public function scopeForCampaign($query, ?string $campaignId)
    {
        if (! $campaignId) {
            return $query;
        }

        return $query->whereIn('list_id', function ($sub) use ($campaignId) {
            $sub->select('list_id')
                ->from('vicidial_lists')
                ->where('campaign_id', $campaignId);
        });
    }
}
