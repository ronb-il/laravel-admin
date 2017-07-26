<?php

return [

	'service' => [

                'email' => [

                	'host' => env('EMAIL_SERVICE_HOST'),

                	'port'=> env('EMAIL_SERVICE_PORT'),

                	'business_rules_changed_recipient' => env('BUSINESS_RULES_CHANGED_RECIPIENT'),

                	'behavioral_rules_changed_recipient' => env('BEHAVIORAL_RULES_CHANGED_RECIPIENT'),

                ],

                'logger' => [
                        'host' => env('LOGGER_SERVICE_HOST'),
                        'port' => env('LOGGER_SERVICE_PORT'),
                ],

                'affiliate' => [

                	'host' => env('AFFILIATE_API_HOST'),

                	'port'=> env('AFFILIATE_API_PORT'),

                        'internal_caching' => env('AFFILIATE_USE_APPLICATION_CACHING', true)

                ],

                'decision' => [

                	'host' => env('DECISION_API_HOST'),

                	'port'=> env('DECISION_API_PORT'),

                ],

                'operation' => [

                        'host' => env('DECISION_API_HOST'),

                        'port'=> env('DECISION_API_PORT'),

                ],

                'catalog' => [

                        'host' => env('CATALOG_API_HOST'),

                        'port'=> env('CATALOG_API_PORT'),

                ],

                'recommendation' => [

                        'host' => env('RECOMMENDATION_API_HOST'),

                        'port'=> env('RECOMMENDATION_API_PORT'),

                ],

	],

        'internal-api-credentials' => [
                'username' => env('INTERNAL_API_USERNAME'),
                'password' => env('INTERNAL_API_PASSWORD')
        ],

];
