<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicidialInboundGroup extends Model
{
    use HasFactory;

    protected $connection = 'asterisk';

    protected $table = 'vicidial_inbound_groups';
    protected $primaryKey = 'group_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * Limit to the groups listed in a campaign's xfer_groups column.
     */
    public function scopeForCampaign($query, ?VicidialCampaign $campaign)
    {
        if (! $campaign) {
            return $query;
        }

        $groupIds = $campaign->inbound_group_ids;

        return $groupIds ? $query->whereIn('group_id', $groupIds)
                         : $query->whereRaw('1 = 0');
    }
}
