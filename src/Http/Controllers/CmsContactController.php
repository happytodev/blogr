<?php

namespace Happytodev\Blogr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Happytodev\Blogr\Events\ContactFormSubmitted;

class CmsContactController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'to_email' => 'nullable|email',
        ]);

        try {
            $toEmail = !empty($data['to_email']) ? $data['to_email'] : config('mail.from.address', 'hello@example.com');

            $emailData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'],
                'message_body' => $data['message'],
            ];

            Mail::raw(
                "Name: {$emailData['name']}\nEmail: {$emailData['email']}\nSubject: {$emailData['subject']}\n\n{$emailData['message_body']}",
                function ($message) use ($toEmail, $emailData) {
                    $message->to($toEmail)
                        ->subject("[Blogr Contact] {$emailData['subject']}")
                        ->replyTo($emailData['email'], $emailData['name']);
                }
            );

            ContactFormSubmitted::dispatch(
                name: $emailData['name'],
                email: $emailData['email'],
                subject: $emailData['subject'],
                message: $emailData['message_body'],
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Blogr contact form failed', [
                'error_type' => get_class($e),
                'error_code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Could not send your message. Please try again later.'),
            ], 500);
        }
    }
}
