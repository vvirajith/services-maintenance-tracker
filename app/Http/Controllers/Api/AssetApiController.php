<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AssetService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class AssetApiController extends Controller
{
    public function __construct(
        private AssetService $assetService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $assets = $this->assetService->getAllAssets();

            return response()->json([
                'success' => true,
                'message' => 'Assets retrieved successfully',
                'data' => $assets
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve assets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'serial_number' => 'required|string|unique:assets,serial_number',
                'description' => 'nullable|string',
            ]);

            $asset = $this->assetService->createAsset($validated);

            return response()->json([
                'success' => true,
                'message' => 'Asset created successfully',
                'data' => $asset
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create asset',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show(int $id): JsonResponse
    {
        try {
            $asset = $this->assetService->getAsset($id);

            return response()->json([
                'success' => true,
                'message' => 'Asset retrieved successfully',
                'data' => $asset
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Asset not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $asset = $this->assetService->updateAsset($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Asset updated successfully',
                'data' => $asset
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
                'message' => 'Failed to update asset',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->assetService->deleteAsset($id);

            return response()->json([
                'success' => true,
                'message' => 'Asset deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete asset',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByStatus(string $status): JsonResponse
    {
        try {
            $validStatuses = ['IN_STOCK', 'IN_USE', 'IN_SERVICE'];
            $status = strtoupper($status);

            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status. Valid statuses: ' . implode(', ', $validStatuses)
                ], 400);
            }

            $assets = $this->assetService->getAssetsByStatus($status);

            return response()->json([
                'success' => true,
                'message' => "Assets with status {$status} retrieved successfully",
                'data' => $assets
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve assets',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
