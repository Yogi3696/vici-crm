<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicidialCampaign extends Model
{
    use HasFactory;

    protected $connection = 'asterisk';

    protected $table = 'vicidial_campaigns';
    protected $primaryKey = 'campaign_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * Inbound group ids linked to this campaign.
     *
     * Vicidial stores them in the xfer_groups text column as a space separated
     * list, with a trailing "-" marker, so it has to be parsed rather than joined.
     */
    public function getInboundGroupIdsAttribute(): array
    {
        return static::parseGroupIds($this->xfer_groups);
    }

    public function inboundGroups()
    {
        return VicidialInboundGroup::whereIn('group_id', $this->inbound_group_ids)
                                   ->orderBy('group_id')
                                   ->get();
    }

    public static function parseGroupIds(?string $xferGroups): array
    {
        return array_values(array_filter(
            preg_split('/\s+/', (string) $xferGroups, -1, PREG_SPLIT_NO_EMPTY),
            fn ($id) => $id !== '-' && $id !== '---NONE---'
        ));
    }
}
