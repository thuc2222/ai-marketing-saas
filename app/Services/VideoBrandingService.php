<?php

namespace App\Services;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;

class VideoBrandingService
{
    public function applyLogo($videoPath, $logoPath, $outputPath)
    {
        // 1. Khởi tạo FFMpeg
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => env('FFMPEG_BINARIES', '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => env('FFPROBE_BINARIES', '/usr/bin/ffprobe'),
        ]);

        $video = $ffmpeg->open(Storage::disk('public')->path($videoPath));

        // 2. Định nghĩa vị trí chèn Logo (Ví dụ: Góc dưới bên phải, cách lề 10px)
        // [0:v][1:v] overlay=W-w-10:H-h-10
        $video->filters()->watermark(Storage::disk('public')->path($logoPath), [
            'position' => 'relative',
            'bottom' => 10,
            'right' => 10,
        ]);

        // 3. Render video mới với định dạng chuẩn X264
        $format = new X264();
        $format->setAudioCodec('aac'); // Đảm bảo âm thanh vẫn giữ nguyên

        $video->save($format, Storage::disk('public')->path($outputPath));

        return $outputPath;
    }
}