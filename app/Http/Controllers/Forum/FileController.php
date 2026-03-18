<?php

namespace App\Http\Controllers\Forum;

use App\Enums\Bucket;
use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use App\Models\TopicToFileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function store(Request $request, string $professionId, string $topicId)
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

        TopicToFileUpload::create([
            'topic_id' => $topicId,
            'file_upload_id' => $upload->id,
        ]);

        return response()->json([
            'path' => $path,
            'id' => $upload->id,
            'name' => $file->getClientOriginalName(),
        ]);
    }

    public function destroy(Request $request, string $professionId, string $topicId, string $fileId)
    {
        $fileUpload = FileUpload::findOrFail($fileId);

        if ($fileUpload->user->id !== $request->user()->id) {
            return back()->with('error', 'Du kannst nur selbst hochgeladene dateien entfernen');
        }

        $disk = Storage::disk('s3-forum');

        $disk->delete($fileUpload->file_path);
        TopicToFileUpload::where('file_upload_id', $fileUpload->id)->delete();
        $fileUpload->delete();
    }
}
