<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */
    'log' => 'daily',
    'log_path' => env('LOG_PATH', '/opt/personali/log'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Collective\Html\HtmlServiceProvider::class,
        Ipunkt\LaravelAnalytics\AnalyticsServiceProvider::class,
        Personali\LaravelConsul\ConsulServiceProvider::class,
        Personali\LaravelService\Catalog\CatalogServiceProvider::class,
        Personali\LaravelService\Logger\LoggerServiceProvider::class,
        Personali\LaravelService\ProfitOptimization\ProfitOptimizationServiceProvider::class,
        Personali\LaravelService\Affiliate\AffiliateServiceProvider::class,
        Personali\LaravelService\Email\EmailServiceProvider::class,
        Personali\LaravelService\Operation\OperationServiceProvider::class,
        Jenssegers\Agent\AgentServiceProvider::class,
        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ],



    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App'       => Illuminate\Support\Facades\App::class,
        'Artisan'   => Illuminate\Support\Facades\Artisan::class,
        'Auth'      => Illuminate\Support\Facades\Auth::class,
        'Blade'     => Illuminate\Support\Facades\Blade::class,
        'Cache'     => Illuminate\Support\Facades\Cache::class,
        'Config'    => Illuminate\Support\Facades\Config::class,
        'Cookie'    => Illuminate\Support\Facades\Cookie::class,
        'Crypt'     => Illuminate\Support\Facades\Crypt::class,
        'DB'        => Illuminate\Support\Facades\DB::class,
        'Eloquent'  => Illuminate\Database\Eloquent\Model::class,
        'Event'     => Illuminate\Support\Facades\Event::class,
        'File'      => Illuminate\Support\Facades\File::class,
        'Gate'      => Illuminate\Support\Facades\Gate::class,
        'Hash'      => Illuminate\Support\Facades\Hash::class,
        'Lang'      => Illuminate\Support\Facades\Lang::class,
        'Log'       => Illuminate\Support\Facades\Log::class,
        'Mail'      => Illuminate\Support\Facades\Mail::class,
        'Password'  => Illuminate\Support\Facades\Password::class,
        'Queue'     => Illuminate\Support\Facades\Queue::class,
        'Redirect'  => Illuminate\Support\Facades\Redirect::class,
        'Redis'     => Illuminate\Support\Facades\Redis::class,
        'Request'   => Illuminate\Support\Facades\Request::class,
        'Response'  => Illuminate\Support\Facades\Response::class,
        'Route'     => Illuminate\Support\Facades\Route::class,
        'Schema'    => Illuminate\Support\Facades\Schema::class,
        'Session'   => Illuminate\Support\Facades\Session::class,
        'Storage'   => Illuminate\Support\Facades\Storage::class,
        'URL'       => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View'      => Illuminate\Support\Facades\View::class,
        'Input'     => Illuminate\Support\Facades\Input::class,
        'Resource'  => App\Resource::class,
        'EmailService' => Personali\LaravelService\Email\EmailServiceFacade::class,
        'AffiliateService' => Personali\LaravelService\Affiliate\AffiliateServiceFacade::class,
        'CatalogService' => Personali\LaravelService\Catalog\CatalogServiceFacade::class,
        'RecommendationService' => Personali\LaravelService\Catalog\CatalogServiceFacade::class,
        'LoggerService' => Personali\LaravelService\Logger\LoggerServiceFacade::class,
        'ProfitOptimizationService' => Personali\LaravelService\ProfitOptimization\ProfitOptimizationServiceFacade::class,
        'OperationService' => Personali\LaravelService\Operation\OperationServiceFacade::class,
        'Agent' => Jenssegers\Agent\Facades\Agent::class,

        /* HTML FORMS */
        'Form'      => Collective\Html\FormFacade::class,
        'Html'      => Collective\Html\HtmlFacade::class,
        'Analytics' => Ipunkt\LaravelAnalytics\AnalyticsFacade::class,
        'Consul' => Personali\LaravelConsul\ConsulFacade::class,
    ],


    /*
    |--------------------------------------------------------------------------
    | List of application roles and permissions
    |--------------------------------------------------------------------------
    |
    | List of roles and associated permissions
    |
    */

    'roles' => [
        'admin' => [],
        'am-viewer' => ['business-rules-view', 'am-reports-view', 'reporting-view', 'behavioral-logs-view', 'operations-view', 'operations-sg-view'],
        'am-editor' => ['business-rules-edit', 'behavior-rules-edit', 'am-reports-view', 'reporting-view', 'variations-edit','smart-pack-edit', 'catalog-edit', 'behavioral-logs-view', 'business-logs-view',
            'operations-edit', 'operations-sg-edit', 'operations-delete', 'operations-name-edit', 'operations-publish', 'operations-add'],
        'customer-viewer' => ['customer-reports-view', 'reporting-view', 'operations-view'],
        'customer-editor' => ['business-rules-edit', 'customer-reports-view', 'reporting-view', 'operations-edit', 'operations-publish'],
        'site-map-viewer' => ['site-map-view']
    ],


    /*
    |--------------------------------------------------------------------------
    | List of reports
    |--------------------------------------------------------------------------
    |
    | Customer reports and Personali reports
    |
    */
    'reports' => [
        'am-reports' => [
            [
                'title' => 'Conversion Uplift',
                'path' => '/views/Internaldashboard-overviewmetricslegendsnewfunnel/Overview',
                'isRealTimeOnly' => false,
                'isDisplayAllAccounts' => true,
                'sub' => [
                    [
                        'title' => 'Overview',
                        //'path' => '/views/Internaldashboardbytabs-overview1/Overview2',
                        'path' => '/views/Internaldashboard-overviewmetricslegendsnewfunnel/Overview',
                        'isRealTimeOnly' => false,
                        'isDisplayAllAccounts' => true,
                        'height' => '3050px',
                    ],
                    /*
                    [
                        'title' => 'Overview Split',
                        //'path' => '/views/Internaldashboardbytabs-overview1/Overview2',
                        'path' => [
                            '/views/Internaldashboardbytabs-overview1/Overview2',
                            '/views/Internaldashboardbytabs-overview1/UVandSKU',
                            '/views/Internaldashboardbytabs-overview1/ConversiontosaleandARPUU'
                        ],
                        'isRealTimeOnly' => false,
                        // 'height' => '600px',
                    ],
                    */
                    [
                        'title' => 'Sales Analytics',
                        'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/Salesanalytics',
                        'isRealTimeOnly' => false,
                        'isDisplayAllAccounts' => true,
                    ],
                    [
                        'title' => 'Exposure',
                        'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels1_1/Salesanalytics',
                        'isRealTimeOnly' => false,
                        'isDisplayAllAccounts' => true,
                        'sub' =>[
                            [
                                'title' => 'Revenue & Sales Exposure',
                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/Exposure',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => false,
                            ],
                            [
                                'title' => 'Users & SKU Exposure - last 3 months',
                                'path' => '/views/SKUsandusersexposure1_1/SKUsandusersexposure',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => false,
                            ],
                            [
                                'title' => 'Users & SKU Exposure - last year',
                                'path' => '/views/SKUsandusersexposurelastyear1_1/SKUsandusersexposure',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => false,
                            ],
                        ]

                    ],
                    [
                        'title' => 'Funnel',
                        'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels1_1/Salesanalytics',
                        'isRealTimeOnly' => false,
                        'isDisplayAllAccounts' => true,
                        'sub' =>[

                            [
                                'title' => 'Funnel Steps',
                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/MainFunnelsteps',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => true,
                            ],
                            [
                                'title' => 'Conversion Funnel - Single Partner',
                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnel-SinglePartner',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => true,
                            ],
                            [
                                'title' => 'Conversion Funnel - Single Partner & Sample Groups',
                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnelSinglepartnerSamplegroups',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => true,
                            ],
                            [
                                'title' => 'Conversion Funnel - Single Partner & Categories',
                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnelSinglepartnerCategories',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => true,
                            ],
                            [
                                'title' => 'Conversion Funnel - Single Partner & Device Type',
                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnelSinglepartnerDevicetype',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => true,
                            ],
                            [
                                'title' => 'Conversion Funnel - Multiple Partners',
                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnel-MultiplePartners',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => true,
                            ],
                        ]
                    ]
                ]
            ],
//            [
//                'title' => 'Profit Optimization',
//                'path' => '/views/profit-optimization',
//                'isRealTimeOnly' => false,
//                'isDisplayAllAccounts' => true,
//                'sub' => [
//                    [
//                        'title' => 'Overview',
//                        'path' => '/views/profit-optimization/Overview',
//                        'isRealTimeOnly' => false,
//                        'isDisplayAllAccounts' => true,
//                        'sub' => [
//                            [
//                                'title' => 'Enabled Revenue',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/Exposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                            [
//                                'title' => 'Enabled Sales',
//                                'path' => '/views/SKUsandusersexposure1_1/SKUsandusersexposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                            [
//                                'title' => 'Incentivized Revenue',
//                                'path' => '/views/SKUsandusersexposurelastyear1_1/SKUsandusersexposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                            [
//                                'title' => 'Incentivized Sales',
//                                'path' => '/views/SKUsandusersexposurelastyear1_1/SKUsandusersexposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ]
//                        ]
//                    ],
//                    [
//                        'title' => 'Sales Analytics',
//                        'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/Salesanalytics',
//                        'isRealTimeOnly' => false,
//                        'isDisplayAllAccounts' => true,
//                    ],
//                    [
//                        'title' => 'Exposure',
//                        'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels1_1/Salesanalytics',
//                        'isRealTimeOnly' => false,
//                        'isDisplayAllAccounts' => true,
//                        'sub' =>[
//                            [
//                                'title' => 'Revenue & Sales Exposure',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/Exposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                            [
//                                'title' => 'Users & SKU Exposure - last 3 months',
//                                'path' => '/views/SKUsandusersexposure1_1/SKUsandusersexposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                            [
//                                'title' => 'Users & SKU Exposure - last year',
//                                'path' => '/views/SKUsandusersexposurelastyear1_1/SKUsandusersexposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                        ]
//
//                    ],
//                    [
//                        'title' => 'Funnel',
//                        'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels1_1/Salesanalytics',
//                        'isRealTimeOnly' => false,
//                        'isDisplayAllAccounts' => true,
//                        'sub' =>[
//
//                            [
//                                'title' => 'Funnel Steps',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/MainFunnelsteps',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Conversion Funnel - Single Partner',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnel-SinglePartner',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Conversion Funnel - Single Partner & Sample Groups',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnelSinglepartnerSamplegroups',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Conversion Funnel - Single Partner & Categories',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnelSinglepartnerCategories',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Conversion Funnel - Single Partner & Device Type',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnelSinglepartnerDevicetype',
//
//                            ],
//                        ]
//                    ]
//                ]
//            ],
//            [
//                'title' => 'User Spend Increase',
//                'path' => '/views/User-Spend-Increase',
//                'isRealTimeOnly' => false,
//                'isDisplayAllAccounts' => true,
//                'height' => '3050px'
//            ],
//            [
//                'title' => 'Loyalty and Retention',
//                'path' => '/views/LoyaltyandRetention',
//                'isRealTimeOnly' => false,
//                'isDisplayAllAccounts' => true,
//                'height' => '3050px'
//            ],
//            [
//                'title' => 'Omni-Channel Support',
//                'path' => '/views/LoyaltyandRetention',
//                'isRealTimeOnly' => false,
//                'isDisplayAllAccounts' => true,
//                'height' => '3050px'
//            ],
//            [
//                'title' => 'PLC Management',
//                'path' => '/views/PLC-Management',
//                'isRealTimeOnly' => false,
//                'isDisplayAllAccounts' => true,
//                'height' => '3050px'
//            ]
        ],
        'customer-reports' => [
            [
                'title' => 'Conversion Uplift',
                'path' => '/views/conversion-uplift',
                'isRealTimeOnly' => false,
                'isDisplayAllAccounts' => true,
                'sub' => [
                    [
                        'title' => 'Overview',
                        'path' => '/views/CustomersdashboardnewFunnel/Overviewcustomers',
                        'isRealTimeOnly' => false,
                        'isDisplayAllAccounts' => true,
                        'height' => '1210px'
                    ],
                    [
                        'title' => 'Sales Analytics',
                        'path' => '/views/CustomersdashboardnewFunnel/Salesanalytics',
                        'isRealTimeOnly' => false,
                        'isDisplayAllAccounts' => true,
                    ],
                    [
                        'title' => 'Funnel Steps',
                        'path' => '/views/CustomersdashboardnewFunnel/MainFunnelsteps',
                        'isRealTimeOnly' => false,
                        'isDisplayAllAccounts' => true,
                    ],
                    [
                        'title' => 'Real-Time',
                        'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels1_1/Salesanalytics',
                        'isRealTimeOnly' => false,
                        'isDisplayAllAccounts' => true,
                        'sub' =>[
                            [
                                'title' => 'Overview',
                                'path' => '/views/Offersreportredshift/Dashboard',
                                'isRealTimeOnly' => true,
                                'isDisplayAllAccounts' => true,
                            ],
                            [
                                'title' => 'Offers report',
                                'path' => '/views/Offersreportredshift/Offerlist',
                                'isRealTimeOnly' => true,
                                'isDisplayAllAccounts' => true,
                            ],
                            [
                                'title' => 'Cart offers report',
                                'path' => '/views/CartOffersreportreplica/Offerlist',
                                'isRealTimeOnly' => true,
                                'isDisplayAllAccounts' => true,
                            ],
                        ]
                    ],
                    [
                        'title' => 'Purchases',
                        'path' => '/views/SalesReports/Purchaselist',
                        'isRealTimeOnly' => false,
                        'isDisplayAllAccounts' => true,
                        'height' => '1600px',
                        'sub' => [
                            [
                                'title' => 'Purchases report',
                                'path' => '/views/SalesReports/Purchaselist',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => true,
                            ],
                            [
                                'title' => 'Top SKUs',
                                'path' => '/views/ItemReportsnew/TopXSkubyRevenue',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => false,
                            ],
                            [
                                'title' => 'Top categories',
                                'path' => '/views/ItemReportsnew/TopXCategorybyRevenue',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => false,
                            ],
                            [
                                'title' => 'SKUs & Conversion by prices',
                                'path' => '/views/ItemReportsnew/Conversionperpricepoint',
                                'isRealTimeOnly' => false,
                                'isDisplayAllAccounts' => false,
                            ]
                        ]
                    ],
                ]
            ],
//            [
//                'title' => 'Profit Optimization',
//                'path' => '/views/profit-optimization',
//                'isRealTimeOnly' => false,
//                'isDisplayAllAccounts' => true,
//                'sub' => [
//                    [
//                        'title' => 'Overview',
//                        'path' => '/views/profit-optimization/Overview',
//                        'isRealTimeOnly' => false,
//                        'isDisplayAllAccounts' => true,
//                        'sub' => [
//                            [
//                                'title' => 'Enabled Revenue',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/Exposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                            [
//                                'title' => 'Enabled Sales',
//                                'path' => '/views/SKUsandusersexposure1_1/SKUsandusersexposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                            [
//                                'title' => 'Incentivized Revenue',
//                                'path' => '/views/SKUsandusersexposurelastyear1_1/SKUsandusersexposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                            [
//                                'title' => 'Incentivized Sales',
//                                'path' => '/views/SKUsandusersexposurelastyear1_1/SKUsandusersexposure',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ]
//                        ]
//                    ],
//                    [
//                        'title' => 'Sales Analytics',
//                        'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/Salesanalytics',
//                        'isRealTimeOnly' => false,
//                        'isDisplayAllAccounts' => true,
//                    ],
//                    [
//                        'title' => 'Funnel',
//                        'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels1_1/Salesanalytics',
//                        'isRealTimeOnly' => false,
//                        'isDisplayAllAccounts' => true,
//                        'sub' =>[
//
//                            [
//                                'title' => 'Funnel Steps',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/MainFunnelsteps',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Conversion Funnel - Single Partner',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnel-SinglePartner',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Conversion Funnel - Single Partner & Sample Groups',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnelSinglepartnerSamplegroups',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Conversion Funnel - Single Partner & Categories',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnelSinglepartnerCategories',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Conversion Funnel - Single Partner & Device Type',
//                                'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels-newfunnels/ConversionFunnelSinglepartnerDevicetype',
//
//                            ],
//                        ]
//                    ],
//                    [
//                        'title' => 'Real-Time',
//                        'path' => '/views/Internaldashboard-SalesanalyticsExposurefunnelstepsperiodmeasuresallfunnels1_1/Salesanalytics',
//                        'isRealTimeOnly' => false,
//                        'isDisplayAllAccounts' => true,
//                        'sub' =>[
//                            [
//                                'title' => 'Overview',
//                                'path' => '/views/Offersreportredshift/Dashboard',
//                                'isRealTimeOnly' => true,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Offers report',
//                                'path' => '/views/Offersreportredshift/Offerlist',
//                                'isRealTimeOnly' => true,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Cart offers report',
//                                'path' => '/views/CartOffersreportreplica/Offerlist',
//                                'isRealTimeOnly' => true,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                        ]
//                    ],
//                    [
//                        'title' => 'Purchases',
//                        'path' => '/views/SalesReports/Purchaselist',
//                        'isRealTimeOnly' => false,
//                        'isDisplayAllAccounts' => true,
//                        'height' => '1600px',
//                        'sub' => [
//                            [
//                                'title' => 'Purchases report',
//                                'path' => '/views/SalesReports/Purchaselist',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => true,
//                            ],
//                            [
//                                'title' => 'Top SKUs',
//                                'path' => '/views/ItemReportsnew/TopXSkubyRevenue',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                            [
//                                'title' => 'Top categories',
//                                'path' => '/views/ItemReportsnew/TopXCategorybyRevenue',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ],
//                            [
//                                'title' => 'SKUs & Conversion by prices',
//                                'path' => '/views/ItemReportsnew/Conversionperpricepoint',
//                                'isRealTimeOnly' => false,
//                                'isDisplayAllAccounts' => false,
//                            ]
//                        ]
//                    ],
//                ]
//            ],
//            [
//                'title' => 'User Spend Increase',
//                'path' => '/views/User-Spend-Increase',
//                'isRealTimeOnly' => false,
//                'isDisplayAllAccounts' => true,
//                'height' => '3050px'
//            ],
//            [
//                'title' => 'Loyalty and Retention',
//                'path' => '/views/LoyaltyandRetention',
//                'isRealTimeOnly' => false,
//                'isDisplayAllAccounts' => true,
//                'height' => '3050px'
//            ],
//            [
//                'title' => 'Omni-Channel Support',
//                'path' => '/views/LoyaltyandRetention',
//                'isRealTimeOnly' => false,
//                'isDisplayAllAccounts' => true,
//                'height' => '3050px'
//            ],
//            [
//                'title' => 'PLC Management',
//                'path' => '/views/PLC-Management',
//                'isRealTimeOnly' => false,
//                'isDisplayAllAccounts' => true,
//                'height' => '3050px'
//            ]
        ]
    ]
];
