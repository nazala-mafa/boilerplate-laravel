<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function __construct(
        private FileUpload $fileUpload,
    ) { }

    public function store(Request $request) {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $path = $request->route('path');
        $file = $request->file('file');

        $filepath = $file->storePublicly($path);
        $filename = basename($filepath);
        $fileurl = Storage::disk('public')->url($filepath);

        $fileUpload = $this->fileUpload->create([
            'original_filename' => $file->getClientOriginalName(),
            'filepath' => $filepath,
            'is_used' => false,
        ]);

        $fileUpload->uploaderable()->associate(auth()->user());

        $fileUpload->save();

        return compact([
            'filepath',
            'filename',
            'fileurl',
        ]);
    }
}
