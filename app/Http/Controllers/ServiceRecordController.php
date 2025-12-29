<?php

namespace App\Http\Controllers;

use App\Services\ServiceRecordService;
use App\Services\AssetService;
use App\Exceptions\AssetNotInStockException;
use Illuminate\Http\Request;
use Exception;

class ServiceRecordController extends Controller
{
    public function __construct(
        private ServiceRecordService $serviceRecordService,
        private AssetService $assetService
    ) {}

    public function index(Request $request)
    {
        try {
            $filter = $request->query('filter', 'all');

            $serviceRecords = $filter === 'active'
                ? $this->serviceRecordService->getActiveServiceRecords()
                : $this->serviceRecordService->getAllServiceRecords();

            return view('service-records.index', compact('serviceRecords', 'filter'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load service records: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $assets = $this->assetService->getAssetsByStatus('IN_STOCK');
            return view('service-records.create', compact('assets'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'asset_id' => 'required|exists:assets,id',
                'handover_date' => 'required|date',
                'handover_note' => 'nullable|string',
                'service_center' => 'nullable|string|max:255',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'image_types.*' => 'nullable|string',
            ]);

            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $images[] = [
                        'file' => $file,
                        'type' => $request->input("image_types.{$index}", 'general'),
                    ];
                }
            }

            $validated['images'] = $images;

            $serviceRecord = $this->serviceRecordService->handoverAsset($validated);

            return redirect()->route('service-records.show', $serviceRecord->id)
                ->with('success', 'Asset handed over to service center successfully!');

        } catch (AssetNotInStockException $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create service record: ' . $e->getMessage());
        }
    }

    public function show(int $id)
    {
        try {
            $serviceRecord = $this->serviceRecordService->getServiceRecord($id);
            return view('service-records.show', compact('serviceRecord'));
        } catch (Exception $e) {
            return back()->with('error', 'Service record not found: ' . $e->getMessage());
        }
    }

    public function pickupForm(int $id)
    {
        try {
            $serviceRecord = $this->serviceRecordService->getServiceRecord($id);

            if ($serviceRecord->pickup_date) {
                return back()->with('error', 'This asset has already been picked up.');
            }

            return view('service-records.pickup', compact('serviceRecord'));
        } catch (Exception $e) {
            return back()->with('error', 'Service record not found: ' . $e->getMessage());
        }
    }

    public function pickup(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'pickup_date' => 'required|date',
                'pickup_note' => 'nullable|string',
            ]);

            $serviceRecord = $this->serviceRecordService->pickupAsset($id, $validated);

            return redirect()->route('service-records.show', $serviceRecord->id)
                ->with('success', 'Asset picked up successfully and returned to stock!');

        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to process pickup: ' . $e->getMessage());
        }
    }

    public function deleteImage(int $imageId)
    {
        try {
            $this->serviceRecordService->deleteServiceImage($imageId);

            return back()->with('success', 'Image deleted successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete image: ' . $e->getMessage());
        }
    }

}
