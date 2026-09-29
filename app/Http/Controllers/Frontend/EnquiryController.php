<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreEnquiryRequest;
use App\Models\Enquiry;
use App\Notifications\NewEnquiry;
use Illuminate\Support\Facades\Notification;
use Throwable;

class EnquiryController extends Controller
{
    public function store(StoreEnquiryRequest $request)
    {
        $enquiry = Enquiry::create($request->safe()->only(['name', 'email', 'phone', 'subject', 'message']) + [
            'source_url' => substr((string) $request->headers->get('referer'), 0, 500) ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        $recipient = settings('contact.contact_email') ?: settings('general.site_email');
        if ($recipient && settings()->bool('contact.notify_on_enquiry')) {
            try {
                Notification::route('mail', $recipient)->notify(new NewEnquiry($enquiry));
            } catch (Throwable $e) {
                report($e); // The enquiry is saved; a mail failure must not fail the visitor's request.
            }
        }

        $message = 'Thank you! Your message has been sent. We will get back to you soon.';

        return $request->expectsJson()
            ? $this->success($message, [], 201)
            : back()->with('success', $message);
    }
}
