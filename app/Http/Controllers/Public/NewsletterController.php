<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Infrastructure\Persistence\Eloquent\Models\NewsletterSubscriberModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse | \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'email'              => ['required', 'email', 'max:255'],
            'newsletter_privacy' => ['accepted'],
        ]);

        $subscriber = NewsletterSubscriberModel::firstOrCreate(
            ['email' => $data['email']],
            [
                'is_active'     => true,
                'subscribed_at' => now(),
            ],
        );

        // If previously unsubscribed, resubscribe
        if (! $subscriber->is_active) {
            $subscriber->subscribe();
        }

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('newsletter_success', true);
    }

    public function unsubscribeForm(): View
    {
        return view('public.newsletter.unsubscribe-form');
    }

    public function unsubscribeByForm(Request $request): View
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriberModel::where('email', $data['email'])->first();

        if ($subscriber && $subscriber->is_active) {
            $subscriber->unsubscribe();
        }

        return view('public.newsletter.unsubscribe');
    }

    public function unsubscribe(string $email): View
    {
        $decodedEmail = base64_decode($email, true);
        $email = $decodedEmail !== false ? $decodedEmail : $email;

        $subscriber = NewsletterSubscriberModel::where('email', $email)->first();

        if ($subscriber && $subscriber->is_active) {
            $subscriber->unsubscribe();
        }

        return view('public.newsletter.unsubscribe');
    }
}
