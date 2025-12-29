<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Collection;

class AssetService extends BaseService
{
    public function createAsset(array $data): Asset
    {
        return $this->executeInTransaction(function () use ($data) {
            $asset = Asset::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'serial_number' => $data['serial_number'],
                'status' => 'IN_STOCK',
                'description' => $data['description'] ?? null,
            ]);

            $this->logAction('Asset created', ['asset_id' => $asset->id]);

            return $asset;
        });
    }
    public function getAllAssets(): Collection
    {
        return Asset::with('serviceRecords')->orderBy('created_at', 'desc')->get();
    }
    public function getAssetsByStatus(string $status): Collection
    {
        return Asset::where('status', $status)
            ->with('serviceRecords')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAsset(int $id): Asset
    {
        return Asset::with(['serviceRecords.images'])->findOrFail($id);
    }

    public function updateAsset(int $id, array $data): Asset
    {
        return $this->executeInTransaction(function () use ($id, $data) {
            $asset = Asset::findOrFail($id);
            $asset->update($data);

            $this->logAction('Asset updated', ['asset_id' => $asset->id]);

            return $asset;
        });
    }

    public function deleteAsset(int $id): bool
    {
        return $this->executeInTransaction(function () use ($id) {
            $asset = Asset::findOrFail($id);

            $this->logAction('Asset deleted', ['asset_id' => $asset->id]);

            return $asset->delete();
        });
    }
}
