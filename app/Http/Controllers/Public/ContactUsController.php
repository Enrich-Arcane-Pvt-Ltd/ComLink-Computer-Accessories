<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ContactUsRequest;
use App\Http\Traits\UtilityTrait;
use App\Models\ContactUs;
use App\Notifications\ContactUs\ContactUsReplyNotification;
use App\Repositories\Interfaces\ContactUsRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class ContactUsController extends Controller
{

    use UtilityTrait;

    protected $contactUs;

    public function __construct(ContactUs $contactUs)
    {
        $this->contactUs = $contactUs;
    }

    // index
    public function index()
    {
        return Inertia::render('Public/ContactUs/Index', []);
    }

    /**
     * Method store
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function store(ContactUsRequest $request)
    {
        $validated = $request->validated();

        $inquiry = $this->contactUs->create($validated);

        Notification::route('mail', $inquiry->email)
            ->notify(new ContactUsReplyNotification($inquiry, $inquiry));

        return redirect()->back()->with('success', 'Your inquiry has been submitted successfully.');
    }
}
