<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Models\ShopSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * صفحة إعدادات المتجر (الاسم، الهاتف، العنوان، الشعار).
     */
    public function edit(): View
    {
        return view('settings.edit', [
            'settings' => ShopSetting::current(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $settings = ShopSetting::current();

        $settings->update($request->safe()->except('logo'));

        if ($request->hasFile('logo')) {
            $settings->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return redirect()->route('settings.edit')->with('success', __('messages.flash.updated'));
    }
}
