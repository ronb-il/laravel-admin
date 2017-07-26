<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'mandrill' => [
        'secret' => env('MANDRILL_SECRET'),
    ],

    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model'  => App\User::class,
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'netotiateapi' => [
        'baseurl' => env('NETOTIATE_API_BASEURL'),
        'authentication' => [],
    ],

    'affiliateapi' => [
        'host' => env('AFFILIATE_API_HOST'),
        'port' => env('AFFILIATE_API_PORT'),
    ],

    'tableauapi' => [
        'authbaseurl' => env('TABLEAU_API_AUTH_BASEURL'),
        'baseurl' => env('TABLEAU_API_BASEURL'),
        'username' => env('TABLEAU_API_USERNAME'),
        'clientip' => env('TABLEAU_API_CLIENTIP'),
    ],

    'email' => [
        'host' => env('EMAIL_SERVICE_HOST'),
        'port' => env('EMAIL_SERVICE_PORT')
    ]

];
