<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ServiceRecordService;
use App\Exceptions\AssetNotInStockException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class ServiceRecordApiController extends Controller
{
    public function __construct(
        private ServiceRecordService $serviceRecordService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $serviceRecords = $this->serviceRecordService->getAllServiceRecords();

            return response()->json([
                'success' => true,
                'message' => 'Service records retrieved successfully',
                'data' => $serviceRecords
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getActive(): JsonResponse
    {
        try {
            $serviceRecords = $this->serviceRecordService->getActiveServiceRecords();

            return response()->json([
                'success' => true,
                'message' => 'Active service records retrieved successfully',
                'data' => $serviceRecords
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active service records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handover(Request $request): JsonResponse
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

            return response()->json([
                'success' => true,
                'message' => 'Asset handed over to service center successfully',
                'data' => $serviceRecord
            ], 201);

        } catch (AssetNotInStockException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Business rule violation',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to handover asset',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $serviceRecord = $this->serviceRecordService->getServiceRecord($id);

            return response()->json([
                'success' => true,
                'message' => 'Service record retrieved successfully',
                'data' => $serviceRecord
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service record not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function pickup(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'pickup_date' => 'required|date',
                'pickup_note' => 'nullable|string',
            ]);

            $serviceRecord = $this->serviceRecordService->pickupAsset($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Asset picked up successfully and returned to stock',
                'data' => $serviceRecord
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to pickup asset',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteImage(int $imageId): JsonResponse
    {
        try {
            $this->serviceRecordService->deleteServiceImage($imageId);

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
