<?php

namespace App\Http\Controllers;

use App\Models\Plan;

class HomeController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)
            ->where('code', '!=', Plan::CODE_TRIAL)
            ->orderBy('sort_order')
            ->get();

        return view('public.landing', compact('plans'));
    }

    public function pricing()
    {
        $plans = Plan::where('is_active', true)
            ->where('code', '!=', Plan::CODE_TRIAL)
            ->orderBy('sort_order')
            ->get();

        return view('public.pricing', compact('plans'));
    }
}
