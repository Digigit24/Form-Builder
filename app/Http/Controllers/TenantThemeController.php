<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantThemeController extends Controller
{
    public function edit(Request $request): View
    {
        return view('theme.edit', [
            'tenant' => $request->user()->tenant,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'font_family' => ['required', 'string', 'max:64'],
        ]);

        $request->user()->tenant->update($data);

        return back()->with('status', 'Theme updated.');
    }
}
