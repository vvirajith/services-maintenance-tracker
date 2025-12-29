<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'handover_date',
        'handover_note',
        'pickup_date',
        'pickup_note',
        'service_center',
    ];

    protected $casts = [
        'handover_date' => 'date',
        'pickup_date' => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class);
    }
}
