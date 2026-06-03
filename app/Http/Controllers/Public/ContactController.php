<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function index(): View
    {
        $settings = SiteSetting::get('contact_settings', []);
        $locale   = app()->getLocale();

        $contact = [
            'title'    => $settings['title'][$locale]    ?? __('messages.contact.title'),
            'subtitle' => $settings['subtitle'][$locale] ?? __('messages.contact.subtitle'),
            'address'  => $settings['address']           ?? __('messages.contact.address'),
            'phone'    => $settings['phone']             ?? __('messages.contact.phone_value'),
            'email'    => $settings['email_public']      ?? __('messages.contact.email_value'),
            'hours'    => $settings['hours'][$locale]    ?? __('messages.contact.hours_value'),
        ];

        return view('public.contact.index', compact('contact'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:2000'],
            'privacy' => ['accepted'],
        ]);

        $settings = SiteSetting::get('contact_settings', []);
        $destination = $settings['contact_destination_email'] ?? config('mail.from.address');

        Mail::to($destination)->send(new ContactFormMail($data));

        return redirect()->route('contact')->with('success', true);
    }
}
