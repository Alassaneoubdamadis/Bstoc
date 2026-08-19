<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function edit(): View
    {
        return view('platform.branding', [
            'appName' => platform_app_name(),
            'logoUrl' => platform_logo_url(),
            'faviconUrl' => platform_favicon_url(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'app_name' => ['required', 'string', 'max:80'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        PlatformSetting::setValue('app_name', $request->input('app_name'));

        if ($request->hasFile('logo')) {
            $dir = public_path('uploads/platform');
            File::ensureDirectoryExists($dir);
            $ext = strtolower($request->file('logo')->getClientOriginalExtension() ?: 'png');
            $filename = 'logo.'.$ext;
            $request->file('logo')->move($dir, $filename);
            $relative = 'uploads/platform/'.$filename;
            PlatformSetting::setValue('logo_path', $relative);
            PlatformSetting::setValue('favicon_path', $relative);
        }

        return redirect()
            ->route('platform.branding.edit')
            ->with('success', 'Nom et logo enregistrés. Le logo est aussi utilisé comme favicon.');
    }
}
