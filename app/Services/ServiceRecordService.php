<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;
use App\Exceptions\AssetNotInStockException;
use App\Models\Asset;
use App\Models\ServiceRecord;
use App\Models\ServiceImage;
use Illuminate\Database\Eloquent\Collection;

class ServiceRecordService extends BaseService
{
    public function __construct(
        private ImageStorageInterface $imageStorage
    ) {}
    public function handoverAsset(array $data): ServiceRecord
    {
        return $this->executeInTransaction(function () use ($data) {
            $asset = Asset::findOrFail($data['asset_id']);

            if (!$asset->isInStock()) {
                throw new AssetNotInStockException(
                    "Cannot handover asset. Current status: {$asset->status}. Only IN_STOCK assets can be sent to service."
                );
            }

            $serviceRecord = ServiceRecord::create([
                'asset_id' => $asset->id,
                'handover_date' => $data['handover_date'],
                'handover_note' => $data['handover_note'] ?? null,
                'service_center' => $data['service_center'] ?? null,
            ]);

            if (isset($data['images']) && is_array($data['images'])) {
                $this->uploadServiceImages($serviceRecord, $data['images']);
            }

            $asset->changeStatus('IN_SERVICE');

            $this->logAction('Asset handed over to service', [
                'asset_id' => $asset->id,
                'service_record_id' => $serviceRecord->id,
            ]);

            return $serviceRecord->load(['asset', 'images']);
        });
    }
    public function pickupAsset(int $serviceRecordId, array $data): ServiceRecord
    {
        return $this->executeInTransaction(function () use ($serviceRecordId, $data) {
            $serviceRecord = ServiceRecord::with('asset')->findOrFail($serviceRecordId);

            $serviceRecord->update([
                'pickup_date' => $data['pickup_date'],
                'pickup_note' => $data['pickup_note'] ?? null,
            ]);

            $serviceRecord->asset->changeStatus('IN_STOCK');

            $this->logAction('Asset picked up from service', [
                'asset_id' => $serviceRecord->asset_id,
                'service_record_id' => $serviceRecord->id,
            ]);

            return $serviceRecord->load(['asset', 'images']);
        });
    }
    private function uploadServiceImages(ServiceRecord $serviceRecord, array $images): void
    {
        foreach ($images as $imageData) {
            if (isset($imageData['file'])) {
                $path = $this->imageStorage->store(
                    $imageData['file'],
                    'service-records'
                );

                ServiceImage::create([
                    'service_record_id' => $serviceRecord->id,
                    'image_path' => $path,
                    'image_type' => $imageData['type'] ?? 'general',
                ]);
            }
        }
    }

    public function getAllServiceRecords(): Collection
    {
        return ServiceRecord::with(['asset', 'images'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function getServiceRecord(int $id): ServiceRecord
    {
        return ServiceRecord::with(['asset', 'images'])->findOrFail($id);
    }
    public function getActiveServiceRecords(): Collection
    {
        return ServiceRecord::with(['asset', 'images'])
            ->whereNull('pickup_date')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function deleteServiceImage(int $imageId): bool
    {
        return $this->executeInTransaction(function () use ($imageId) {
            $image = ServiceImage::findOrFail($imageId);

            $this->imageStorage->delete($image->image_path);

            return $image->delete();
        });
    }
}
