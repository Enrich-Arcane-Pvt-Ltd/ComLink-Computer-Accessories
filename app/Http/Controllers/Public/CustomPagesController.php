<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Traits\UtilityTrait;
use App\Repositories\Interfaces\CustomPageRepositoryInterface;
use Inertia\Response;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomPagesController extends Controller
{
    use UtilityTrait;

    protected $customPageRepositoryInterface;

    public function __construct(CustomPageRepositoryInterface $customPageRepositoryInterface)
    {
        $this->customPageRepositoryInterface = $customPageRepositoryInterface;
    }

    /**
     * Admin Side: Edit Custom Pages
     */
    public function edit()
    {
        // FIX: DB එකේ තියෙන නිවැරදි key එක 'terms_conditions' ලෙස වෙනස් කරන ලදි
        $terms_and_conditions = $this->customPageRepositoryInterface->findByColumn(['key' => 'terms_conditions']);
        $privacy_policy = $this->customPageRepositoryInterface->findByColumn(['key' => 'privacy_policy']);

        // Record එකක් නැත්නම් විතරක් default එකක් සාදයි
        if (!$terms_and_conditions) {
            $terms_and_conditions = $this->customPageRepositoryInterface->create([
                'type' => 'page',
                'key' => 'terms_conditions',
                'value' => '',
            ]);
        }
        if (!$privacy_policy) {
            $privacy_policy = $this->customPageRepositoryInterface->create([
                'type' => 'page',
                'key' => 'privacy_policy',
                'value' => '',
            ]);
        }

        $page = collect([
            'terms_conditions' => $terms_and_conditions,
            'privacy_policy' => $privacy_policy,
        ]);

        return Inertia::render('Admin/CustomPages/Index', [
            'page' => $page,
            'type' => 'edit',
        ]);
    }

    /**
     * Admin Side: Update Custom Pages
     */
    public function updatePages(Request $request)
    {
        $data = $request->all();
        foreach ($data as $key => $value) {
            $page = $this->customPageRepositoryInterface->findByColumn(['key' => $key]);
            if ($page) {
                $page->update([
                    'value' => $value,
                ]);
            }
        }
        return redirect()->back()->with('success', ['Pages updated successfully', $this->randomKey()]);
    }


    // PUBLIC SIDE

    /**
     * Public View: Terms and Conditions
     *
     * @return Response
     */
    public function terms(): Response
    {
        // FIX: findByColumn එක මඟින් කෙලින්ම record එක ලබා ගනී
        $terms = $this->customPageRepositoryInterface->findByColumn(['key' => 'terms_conditions']);

        return Inertia::render('Public/TermsOfService/Index', [
            'terms' => $terms
        ]);
    }

    /**
     * Public View: Privacy Policy
     *
     * @return Response
     */
    public function privacy(): Response
    {
        // FIX: findByColumn එක මඟින් කෙලින්ම record එක ලබා ගනී
        $privacy = $this->customPageRepositoryInterface->findByColumn(['key' => 'privacy_policy']);

        return Inertia::render('Public/PrivacyPolicy/Index', [
            'privacy' => $privacy
        ]);
    }
}
