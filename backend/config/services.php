<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'googleApi' => [
        'api_key' => env('GOOGLE_API_KEY'),
    ],

    'google_places' => [

        // API 엔드포인트
        'endpoints' => [
            'autocomplete' => 'https://places.googleapis.com/v1/places:autocomplete',
            'text_search' => 'https://places.googleapis.com/v1/places:searchText',
            'reverse_geocoding' => 'https://maps.googleapis.com/maps/api/geocode/json',
            'nearby' => 'https://places.googleapis.com/v1/places:searchNearby',
        ],

        // FieldMask
        'field_masks' => [
            'search' => 'places.id,places.displayName,places.formattedAddress,places.location,places.primaryType,nextPageToken',
            'nearby' => 'places.id,places.displayName,places.formattedAddress,places.location,places.primaryType',
            'place_details' => 'id,displayName,formattedAddress,location,primaryType',
        ],
    ],

];
