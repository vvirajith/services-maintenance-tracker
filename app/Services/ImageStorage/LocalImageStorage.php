<?php

namespace App\Services\ImageStorage;

use App\Contracts\ImageStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LocalImageStorage implements ImageStorageInterface
{
    public function store(UploadedFile $file, string $directory): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }
    public function delete(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }

    public function url(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}
