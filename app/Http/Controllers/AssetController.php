<?php

namespace App\Http\Controllers;

use App\Services\AssetService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class AssetController extends Controller
{
    public function __construct(
        private AssetService $assetService
    ) {}

    public function index(Request $request)
    {
        try {
            $status = $request->query('status');

            $assets = $status
                ? $this->assetService->getAssetsByStatus($status)
                : $this->assetService->getAllAssets();

            return view('assets.index', compact('assets', 'status'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load assets: ' . $e->getMessage());
        }
    }
    public function create()
    {
        return view('assets.create');
    }
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'serial_number' => 'required|string|unique:assets,serial_number',
                'description' => 'nullable|string',
            ]);

            $asset = $this->assetService->createAsset($validated);

            return redirect()->route('assets.index')
                ->with('success', 'Asset created successfully!');
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create asset: ' . $e->getMessage());
        }
    }
    public function show(int $id)
    {
        try {
            $asset = $this->assetService->getAsset($id);
            return view('assets.show', compact('asset'));
        } catch (Exception $e) {
            return back()->with('error', 'Asset not found: ' . $e->getMessage());
        }
    }
    public function edit(int $id)
    {
        try {
            $asset = $this->assetService->getAsset($id);
            return view('assets.edit', compact('asset'));
        } catch (Exception $e) {
            return back()->with('error', 'Asset not found: ' . $e->getMessage());
        }
    }
    public function update(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $asset = $this->assetService->updateAsset($id, $validated);

            return redirect()->route('assets.show', $id)
                ->with('success', 'Asset updated successfully!');
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update asset: ' . $e->getMessage());
        }
    }
    public function destroy(int $id)
    {
        try {
            $this->assetService->deleteAsset($id);

            return redirect()->route('assets.index')
                ->with('success', 'Asset deleted successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete asset: ' . $e->getMessage());
        }
    }

    public function getByStatus(string $status): JsonResponse
    {
        try {
            $assets = $this->assetService->getAssetsByStatus(strtoupper($status));
            return response()->json(['data' => $assets]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
