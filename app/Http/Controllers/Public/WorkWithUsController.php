<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Infrastructure\Persistence\Eloquent\Models\JobApplication;
use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Mail\JobApplicationMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class WorkWithUsController extends Controller
{
    public function index(): View
    {
        $settings = SiteSetting::get('careers_page', []) ?? [];
        $locale = app()->getLocale();

        return view('public.pages.work-with-us', compact('settings', 'locale'));
    }

    public function store(StoreJobApplicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $settings = SiteSetting::get('careers_page', []) ?? [];
        $locale = app()->getLocale();

        // Handle CV upload
        $cvPath = null;
        if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $ext = $file->extension();
            $filename = Str::uuid().'.'.$ext;
            $cvPath = $file->storeAs('cv-uploads', $filename, 'private');
        }

        // Persist to DB
        $application = JobApplication::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'],
            'message' => $validated['message'],
            'cv_path' => $cvPath,
            'privacy_accepted' => true,
            'ip_address' => $request->ip(),
        ]);

        // Send email
        try {
            Mail::send(new JobApplicationMail($application, $settings));
        } catch (\Exception $e) {
            report($e);

            $successMessage = $settings['form_success_message'][$locale]
                ?? __('messages.careers.form_success');

            return redirect()
                ->to(LaravelLocalization::getLocalizedURL($locale, route('careers.index')))
                ->with('success', $successMessage)
                ->with('warning', __('messages.careers.email_notification_failed'))
                ->withInput();
        }

        // Flash success
        $successMessage = $settings['form_success_message'][$locale]
            ?? __('messages.careers.form_success');

        return redirect()
            ->to(LaravelLocalization::getLocalizedURL($locale, route('careers.index')))
            ->with('success', $successMessage);
    }
}
