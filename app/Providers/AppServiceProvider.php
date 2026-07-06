<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $firebase = [
            'XSRF-TOKEN-AK' => env('FIREBASE_APIKEY', env('FIREBASE_API_KEY', 'AIzaSyCYaa2ILmCFYLhWNEE3MWF6h74ECqhWZ38')),
            'XSRF-TOKEN-AD' => env('FIREBASE_AUTH_DOMAIN', 'erbete-putra.firebaseapp.com'),
            'XSRF-TOKEN-DU' => env('FIREBASE_DATABASE_URL', 'https://erbete-putra-default-rtdb.asia-southeast1.firebasedatabase.app'),
            'XSRF-TOKEN-PI' => env('FIREBASE_PROJECT_ID', 'erbete-putra'),
            'XSRF-TOKEN-SB' => env('FIREBASE_STORAGE_BUCKET', 'erbete-putra.appspot.com'),
            'XSRF-TOKEN-MS' => env('FIREBASE_MESSAAGING_SENDER_ID', env('FIREBASE_MESSAGING_SENDER_ID', '620343172253')),
            'XSRF-TOKEN-AI' => env('FIREBASE_APP_ID', '1:620343172253:web:a7acb1cdd998c095414874'),
            'XSRF-TOKEN-MI' => env('FIREBASE_MEASUREMENT_ID', 'G-8FNRCWJ466'),
        ];

        foreach ($firebase as $cookieName => $value) {
            $value = (string) $value;
            if ($value !== '') {
                setcookie($cookieName, bin2hex($value), time() + 3600, "/");
            }
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
