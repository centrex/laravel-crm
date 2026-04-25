<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Controllers;

use Centrex\Crm\Support\EmailSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Routing\Controller;

class EmailSettingsController extends Controller
{
    public function edit(): View
    {
        return view('crm::email-settings', [
            'emailSettings' => EmailSettings::data(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled'             => ['nullable', 'boolean'],
            'from_address'        => ['required', 'email', 'max:255'],
            'from_name'           => ['required', 'string', 'max:255'],
            'reply_to'            => ['required', 'email', 'max:255'],
            'default_owner_email' => ['required', 'email', 'max:255'],
            'lead_subject'        => ['required', 'string', 'max:255'],
            'deal_subject'        => ['required', 'string', 'max:255'],
            'activity_subject'    => ['required', 'string', 'max:255'],
        ]);

        EmailSettings::update($validated);

        return back()->with('crm_success', 'CRM email settings updated.');
    }
}
