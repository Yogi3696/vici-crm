<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicidialCampaign extends Model
{
    use HasFactory;

    protected $table = 'vicidial_campaigns';
    protected $primaryKey = 'campaign_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
}
