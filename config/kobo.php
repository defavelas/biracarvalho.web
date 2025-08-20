<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Kobo API URL
    |--------------------------------------------------------------------------
    |
    | Here you may specify the URL for the Kobo API.
    |
    */
    'api_url' => env('KOBO_API_URL', 'https://kf.kobotoolbox.org'),

    /*
    |--------------------------------------------------------------------------
    | Kobo API Token (Credentials)
    |--------------------------------------------------------------------------
    |
    | Here you may specify the token for the Kobo API, used to authenticate
    | requests as Bearer Token.
    |
    */
    'api_token' => env('KOBO_API_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Kobo API Form IDs
    |--------------------------------------------------------------------------
    |
    | Here you may specify the form IDs for the Kobo API.
    |
    */
    'accessible_form_id' => 'ars3r7q7nftPBH857jBfp8',

    /*
    |--------------------------------------------------------------------------
    | Kobo API Non Accessible Form ID
    |--------------------------------------------------------------------------
    |
    | Here you may specify the form ID for the non accessible locations
    | collected by the KoboToolbox survey.
    |
    */
    'non_accessible_form_id' => 'acUMxwbvhW25C28vkiZryd',

    /*
    |--------------------------------------------------------------------------
    | Kobo API Values Mapping
    |--------------------------------------------------------------------------
    |
    | Here you may specify the values mapping for the Kobo API.
    | This is used to map the values from the Kobo API to the values in the
    | database.
    |
    */
    'import' => [
        'values' => [
            'n_o' => 'Não',
            'sim' => 'Sim',
        ],
    ],
];