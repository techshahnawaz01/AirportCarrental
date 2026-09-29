<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'website' => ['prohibited'],
        ], ['website.prohibited' => 'Unable to subscribe.']);

        Subscriber::firstOrCreate(['email' => mb_strtolower($data['email'])], ['ip_address' => $request->ip()]);

        $message = 'Thanks for subscribing!';

        return $request->expectsJson() ? $this->success($message, [], 201) : back()->with('success', $message);
    }
}
