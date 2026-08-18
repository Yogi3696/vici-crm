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

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(VicidialCampaign::class, 'campaign_id', 'campaign_id');
    }
}
