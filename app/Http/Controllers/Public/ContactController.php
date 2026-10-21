<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Notifications\ContactInquiryConfirmationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    /**
     * Contact පිටුව පෙන්වීම
     */
    public function index(): Response
    {
        return Inertia::render('Contact/Index', [
            'title' => 'Contact Us',
        ]);
    }

    /**
     * Form එක Submit කරන විට Handle කිරීම
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:9'],
            'subject' => ['required', 'string', 'min:3', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'name.required' => 'Please enter your name.',
            'name.min' => 'Name must be at least 2 characters.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid active email address.',
            'phone.required' => 'Please enter your contact number.',
            'phone.regex' => 'Please enter a valid phone number format.',
            'subject.required' => 'Please enter a subject.',
            'message.required' => 'Please enter your message.',
            'message.min' => 'The message must be at least 10 characters long.',
        ]);

        try {
            // 💾 ඩේටාබේස් එකට ඇතුළත් කිරීම
            $inquiry = ContactInquiry::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'status' => 'pending', // Default status
            ]);
            Notification::route('mail', $inquiry->email)
                ->notify(new ContactInquiryConfirmationNotification($inquiry));

            return redirect()->back()->with('success', 'Thank you! Your message has been sent successfully. A confirmation email has been sent to your inbox.');
        } catch (\Exception $e) {
            Log::error('Contact Submission Error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong. Please try again later.');
        }
    }
}
