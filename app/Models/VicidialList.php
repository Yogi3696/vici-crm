<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VicidialList extends Model
{
    use HasFactory;

    protected $connection = 'asterisk';

    protected $table = 'vicidial_lists';
    protected $primaryKey = 'list_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'list_id' => 'integer',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'list_id', 'list_id');
    }

    /**
     * Match the list id, name or description against a search term.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('list_id', 'like', "%{$term}%")
              ->orWhere('list_name', 'like', "%{$term}%")
              ->orWhere('list_description', 'like', "%{$term}%");
        });
    }

    /**
     * Limit the query to one campaign.
     */
    public function scopeForCampaign($query, ?string $campaignId)
    {
        if (! $campaignId) {
            return $query;
        }

        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Limit the query by the active flag, which Vicidial stores as Y or N.
     */
    public function scopeActive($query, ?string $active)
    {
        if (! in_array($active, ['Y', 'N'], true)) {
            return $query;
        }

        return $query->where('active', $active);
    }

    /**
     * Whether the list is switched on for dialling.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->active === 'Y';
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(VicidialCampaign::class, 'campaign_id', 'campaign_id');
    }
}
