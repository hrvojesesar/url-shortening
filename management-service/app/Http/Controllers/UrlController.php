<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Url;
use App\Jobs\SendUrlToRabbitMQ;
use Illuminate\Support\Facades\Log;

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

        $message = [
            'event' => 'url_created',
            'data' => [
                'id' => $url->id,
                'real_url' => $url->realURL,
                'short_url' => $url->shortURL,
                'timestamp' => now()->toDateTimeString(),
                'action' => 'create'
            ]
        ];

        SendUrlToRabbitMQ::dispatch($message);

        return response()->json([
            'id' => $url->id,
            'realURL' => $url->realURL,
            'shortURL' => url('http://localhost:8001') . '/' . $url->shortURL,
        ]);
    }

    public function deleteURL($id)
    {
        $url = Url::where('id', $id)->first();
        if (!$url) {
            return response()->json([
                'status' => 'error',
                'message' => 'URL not found',
            ]);
        }

        $url->delete();

        $message = [
            'event' => 'url_deleted',
            'data' => [
                'id' => $url->id,
                'real_url' => $url->realURL,
                'short_url' => $url->shortURL,
                'timestamp' => now()->toDateTimeString(),
                'action' => 'delete'
            ]
        ];

        SendUrlToRabbitMQ::dispatch($message);


        return response()->json([
            'status' => 'success',
            'message' => 'URL deleted',
        ]);
    }
}
