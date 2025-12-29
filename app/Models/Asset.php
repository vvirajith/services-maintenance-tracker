<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'serial_number',
        'status',
        'description',
    ];

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function isInStock(): bool
    {
        return $this->status === 'IN_STOCK';
    }

    public function changeStatus(string $status): void
    {
        $this->status = $status;
        $this->save();
    }
}
