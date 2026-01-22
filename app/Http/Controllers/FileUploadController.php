<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $path = $request->route('path');
        $file = $request->file('file');

        $filepath = $file->storePublicly($path);
        $filename = basename($filepath);
        $fileurl = Storage::disk('public')->url($filepath);

        return compact([
            'filepath',
            'filename',
            'fileurl',
        ]);
    }
}
