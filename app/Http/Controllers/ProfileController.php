<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $address = $user->addresses()->where('is_default', true)->first()
            ?? $user->addresses()->first();

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'address' => $address,
        ]);
    }

    /**
     * Update the user's profile information and address.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['name']) && empty($validated['first_name'])) {
            $parts = explode(' ', trim((string) $validated['name']), 2);
            $validated['first_name'] = $parts[0] ?? '';
            $validated['last_name'] = $parts[1] ?? ($validated['last_name'] ?? '');
            unset($validated['name']);
        }

        // Separate address data
        $addressKeys = ['address_line_1', 'address_line_2', 'city', 'postal_code', 'state', 'country'];
        $hasAddressInput = false;
        foreach ($addressKeys as $k) {
            if (!empty($validated[$k])) {
                $hasAddressInput = true;
                break;
            }
        }

        $addressData = [
            'address_line_1' => $validated['address_line_1'] ?? '',
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'] ?? '',
            'postal_code' => $validated['postal_code'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? 'Sri Lanka',
        ];

        $userData = collect($validated)->except($addressKeys)->all();

        $request->user()->fill($userData);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // Update or create default address
        if ($hasAddressInput) {
            $address = $request->user()->addresses()->where('is_default', true)->first()
                ?? $request->user()->addresses()->first();

            $fullAddressPayload = array_merge($addressData, [
                'first_name' => $request->user()->first_name ?? 'Customer',
                'last_name' => $request->user()->last_name ?? '',
                'phone' => $request->user()->phone,
                'secondary_phone' => $request->user()->secondary_phone,
                'type' => 'shipping',
                'is_default' => true,
            ]);

            if ($address) {
                $address->update($fullAddressPayload);
            } else {
                $request->user()->addresses()->create($fullAddressPayload);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        $user = $request->user();

        // Delete old avatar if present on public disk
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar_url = $path;
        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profile photo updated successfully!');
    }

    /**
     * Remove the user's custom avatar.
     */
    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $user->avatar_url = null;
        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profile photo removed successfully.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
