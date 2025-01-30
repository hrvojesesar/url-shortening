<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;



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
    $realURL = Redis::get($shortURL);

    if ($realURL) {
        return redirect()->to($realURL, 302);
    }

    return response()->json(['error' => 'Not Found'], 404);
});
