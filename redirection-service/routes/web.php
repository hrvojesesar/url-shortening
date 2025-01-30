<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Response;



Route::get('/', function () {
    return view('welcome');
});


// Route::get('/redis-test', function () {
//     try {
//         Redis::ping(); // Pokušava uspostaviti vezu s Redisom
//         return 'Uspješno ste spojeni na Redis!';
//     } catch (\Exception $e) {
//         return 'Povezivanje s Redisom nije uspjelo: ' . $e->getMessage();
//     }
// });



Route::get('/{shortURL}', function ($shortURL) {
    $rateLimitKey = 'shortURL:' . $shortURL;

    if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
        return response()->json([
            'error' => 'Rate limit exceeded. Please try again later.'
        ], Response::HTTP_TOO_MANY_REQUESTS);
    }
    RateLimiter::hit($rateLimitKey, 120);

    $realURL = Redis::get($shortURL);

    if ($realURL) {
        return redirect()->to($realURL, Response::HTTP_FOUND);
    }

    return response()->json([
        'error' => 'Short URL not found.'
    ], Response::HTTP_NOT_FOUND);
});
