<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicidialUser extends Model
{
    use HasFactory;

    protected $connection = 'asterisk';

    protected $table = 'vicidial_users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $guarded = [];

    /**
     * Match the user or full name against a search term.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('user', 'like', "%{$term}%")
              ->orWhere('full_name', 'like', "%{$term}%");
        });
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
}
