<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface ImageStorageInterface
{
    public function store(UploadedFile $file, string $directory): string;

    public function delete(string $path): bool;

    public function url(string $path): string;
}
