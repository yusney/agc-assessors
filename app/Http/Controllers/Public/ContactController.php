<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function index(): View
    {
        return view('public.contact.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:2000'],
            'privacy' => ['accepted'],
        ]);

        // TODO: dispatch a ContactFormSubmitted event / mail
        // Mail::to(config('mail.to.address'))->send(new ContactFormMail($data));

        return redirect()->route('contact')->with('success', true);
    }
}
