<?php

namespace App\Http\Controllers;

use App\Models\WhatsappLog;
use Illuminate\Http\Request;

class WhatsappLogController extends Controller
{
    public function index(Request $request)
    {
        $event = $request->string('event')->toString();
        $status = $request->string('status')->toString();

        $logs = WhatsappLog::with('customer', 'invoice')
            ->when($event, fn ($q) => $q->where('event', $event))
            ->when($status === 'success', fn ($q) => $q->where('success', true))
            ->when($status === 'failed', fn ($q) => $q->where('success', false))
            ->latest()
            ->limit(1000)
            ->paginate(50)
            ->withQueryString();

        return view('whatsapp.logs.index', compact('logs', 'event', 'status'));
    }
}
