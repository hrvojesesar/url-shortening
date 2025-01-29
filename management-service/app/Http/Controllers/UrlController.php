<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Url;

class UrlController extends Controller
{
    public function createShortURL(Request $request)
    {
        $request->validate([
            'realURL' => 'required|url'
        ]);

        $existingUrl = Url::where('realURL', $request->input('realURL'))->first();

        if ($existingUrl) {
            return response()->json([
                'id' => $existingUrl->id,
                'realURL' => $existingUrl->realURL,
                'shortURL' => url('/') . '/' . $existingUrl->shortURL,
            ]);
        }

        $shortURL = hash('crc32b', $request->input('realURL'));
        while (Url::where('shortURL', $shortURL)->exists()) {
            $shortURL = hash('crc32b', $request->input('realURL'));
        }


        $url = Url::create([
            'realURL' => $request->input('realURL'),
            'shortURL' => $shortURL
        ]);

        return response()->json([
            'id' => $url->id,
            'realURL' => $url->realURL,
            'shortURL' => url('/') . '/' . $url->shortURL,
        ]);
    }

    public function redirectToRealURL($shortURL)
    {
        $url = Url::where('shortURL', $shortURL)->first();
        if (!$url) {
            return response()->json([
                'status' => 'error',
                'message' => 'URL not found',
            ]);
        }

        return redirect($url->realURL);
    }
}
