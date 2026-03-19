<?php

namespace App\Http\Controllers\Settings;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProfileAvatarController
{
    public function presign(Request $request)
    {
        $request->validate([
            'contentType' => 'required|string|in:image/png,image/jpeg,image/jpg,image/webp',
        ]);
        $user = Auth::user();

        // Ordner (ID oder Slug)
        $folder = $user->avatar_folder ?? $user->id;
        $bucket   = config('filesystems.disks.avatars.bucket');
        $endpoint = rtrim(config('filesystems.disks.avatars.endpoint'), '/');
        $region   = config('filesystems.disks.avatars.region', 'us-east-1');
        $key      = "{$folder}/avatar.png";
        $s3 = new S3Client([
            'version'                 => 'latest',
            'region'                  => $region,
            'endpoint'                => $endpoint,
            'use_path_style_endpoint' => true,
            'credentials'             => [
                'key'    => config('filesystems.disks.avatars.key'),
                'secret' => config('filesystems.disks.avatars.secret'),
            ],
        ]);
        $cmd = $s3->getCommand('PutObject', [
            'Bucket'      => $bucket,
            'Key'         => $key,
            'ContentType' => $request->contentType,
        ]);
        $signed = $s3->createPresignedRequest($cmd, '+5 minutes');
        $publicUrl = "{$endpoint}/{$bucket}/{$key}";
        $user->touch();
        return response()->json([
            'url'        => (string)$signed->getUri(),
            'publicUrl'  => $publicUrl,
            'expiresIn'  => 300,
            'updated_at' => $user->updated_at,
        ]);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();
        $folder = $user->avatar_folder ?? $user->id;
        $bucket   = config('filesystems.disks.avatars.bucket');
        $endpoint = rtrim(config('filesystems.disks.avatars.endpoint'), '/');
        $region   = config('filesystems.disks.avatars.region', 'us-east-1');
        $key      = "{$folder}/avatar.png";
        $s3 = new S3Client([
            'version'                 => 'latest',
            'region'                  => $region,
            'endpoint'                => $endpoint,
            'use_path_style_endpoint' => true,
            'credentials'             => [
                'key'    => config('filesystems.disks.avatars.key'),
                'secret' => config('filesystems.disks.avatars.secret'),
            ],
        ]);
        try {
            $s3->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $key,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
        $user->touch();
        return response()->json([
            'ok'         => true,
            'updated_at' => $user->updated_at,
        ]);
    }
}