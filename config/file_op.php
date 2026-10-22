<?php

declare(strict_types=1);

return [
    'uploads' => [
        'max_image_kb' => (int) env('FILE_OP_MAX_IMAGE_KB', 10240),
        'max_video_kb' => (int) env('FILE_OP_MAX_VIDEO_KB', 512000),
    ],

    'image' => [
        'default_quality' => (int) env('FILE_OP_IMAGE_QUALITY', 80),
        'max_dimension' => (int) env('FILE_OP_MAX_IMAGE_DIMENSION', 8000),
    ],

    'video' => [
        'ffmpeg_binary' => env('FFMPEG_BINARY', 'ffmpeg'),
        'ffprobe_binary' => env('FFPROBE_BINARY', 'ffprobe'),
        'probe_timeout' => (int) env('FILE_OP_PROBE_TIMEOUT', 30),
        'processing_timeout' => (int) env('FILE_OP_VIDEO_TIMEOUT', 900),
        'default_crf' => (int) env('FILE_OP_VIDEO_CRF', 23),
        'default_preset' => env('FILE_OP_VIDEO_PRESET', 'medium'),
    ],

    'retention' => [
        'days' => (int) env('FILE_OP_RETENTION_DAYS', 7),
    ],
];
