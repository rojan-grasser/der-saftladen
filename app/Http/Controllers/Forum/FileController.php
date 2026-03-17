<?php

namespace App\Http\Controllers\Forum;

use App\Enums\Bucket;
use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:524288', // 512MB max
        ]);

        $disk = Storage::disk('s3-forum');

        $id = \Str::uuid();
        $file = $request->file('file');

        $path = $disk->putFileAs($id, $file, $file->getClientOriginalName());

        $upload = FileUpload::create([
            'user_id' => $request->user()->id,
            'bucket' => Bucket::FORUM,
            'file_path' => $path,
            'size' => $file->getSize() ?? 0,
        ]);

        return response()->json([
            'path' => $path,
            'id' => $upload->id,
            'name' => $file->getClientOriginalName(),
        ]);
    }
}
