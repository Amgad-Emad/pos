<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * صفحة تغيير كلمة المرور للمستخدم الحالي.
     */
    public function edit(): View
    {
        return view('profile.edit');
    }
}
