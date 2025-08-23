<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Video Processing Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for video processing,
    | including AWS MediaConvert settings and output formats.
    |
    */

    'aws' => [
        'mediaconvert' => [
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('AWS_MEDIACONVERT_ENDPOINT'),
            'role_arn' => env('AWS_MEDIACONVERT_ROLE_ARN'),
            'queue' => env('AWS_MEDIACONVERT_QUEUE', 'Default'),
        ],
        's3' => [
            'input_bucket' => env('AWS_BUCKET'),
            'output_bucket' => env('AWS_BUCKET'),
            'output_prefix' => 'processed-videos/',
        ],
    ],

    'output_formats' => [
        'mp4' => [
            'enabled' => true,
            'codec' => 'H_264',
            'bitrate' => 5000000, // 5 Mbps
            'width' => 1920,
            'height' => 1080,
            'framerate' => 30,
        ],
        'hls' => [
            'enabled' => true,
            'segment_duration' => 10,
            'playlist_type' => 'VOD',
            'variants' => [
                [
                    'name' => 'high',
                    'bitrate' => 5000000,
                    'width' => 1920,
                    'height' => 1080,
                ],
                [
                    'name' => 'medium',
                    'bitrate' => 2500000,
                    'width' => 1280,
                    'height' => 720,
                ],
                [
                    'name' => 'low',
                    'bitrate' => 1000000,
                    'width' => 854,
                    'height' => 480,
                ],
            ],
        ],
    ],

    'thumbnails' => [
        'enabled' => true,
        'format' => 'JPEG',
        'width' => 1280,
        'height' => 720,
        'interval' => 10, // seconds
        'max_captures' => 10,
    ],

    'processing' => [
        'timeout' => 3600, // 1 hour in seconds
        'max_retries' => 3,
        'retry_delay' => 300, // 5 minutes in seconds
    ],
];