<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SpamProtection
{
    private const MIN_SECONDS = 3;

    public function handle(Request $request, Closure $next): Response
    {
        // Layer 1: Honeypot — bots fill hidden fields, humans don't see them
        if ($request->filled('_h_url') || $request->filled('_h_name')) {
            return $this->reject($request);
        }

        // Layer 2: Time token — bots submit instantly, humans take at least 3s
        $token = $request->input('_form_token');

        if (! $token) {
            return $this->reject($request);
        }

        try {
            $timestamp = (int) decrypt($token);
        } catch (\Throwable) {
            return $this->reject($request);
        }

        if ((now()->timestamp - $timestamp) < self::MIN_SECONDS) {
            return $this->reject($request);
        }

        return $next($request);
    }

    private function reject(Request $request): Response
    {
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]); // Silent — don't reveal detection
        }

        return redirect()->back()->withInput();
    }
}
