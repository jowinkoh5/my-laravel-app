<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Session authentication for the admin dashboard.
 */
class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate the credentials and start an authenticated session.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Issue a fresh session id so a token captured before login cannot be
        // replayed afterwards.
        $request->session()->regenerate();

        return redirect()->intended(route('admin.products.index'));
    }

    /**
     * Log the user out and invalidate the session entirely.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('catalog.index');
    }
}
