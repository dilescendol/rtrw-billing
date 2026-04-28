<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappNotifier;
use Illuminate\Http\Request;

class WhatsappTemplateController extends Controller
{
    public function index()
    {
        $tenantId = app('current_tenant_id');
        $templates = WhatsappTemplate::where('tenant_id', $tenantId)->get()->keyBy('event');

        return view('whatsapp.templates.index', compact('templates'));
    }

    public function edit(string $event)
    {
        if (! in_array($event, WhatsappTemplate::EVENTS, true)) {
            abort(404);
        }
        $tenantId = app('current_tenant_id');
        $template = WhatsappTemplate::firstOrNew(
            ['tenant_id' => $tenantId, 'event' => $event],
            ['message' => WhatsappTemplate::DEFAULTS[$event] ?? '', 'is_active' => true]
        );

        return view('whatsapp.templates.edit', compact('template', 'event'));
    }

    public function update(Request $request, string $event)
    {
        if (! in_array($event, WhatsappTemplate::EVENTS, true)) {
            abort(404);
        }
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $tenantId = app('current_tenant_id');
        WhatsappTemplate::updateOrCreate(
            ['tenant_id' => $tenantId, 'event' => $event],
            [
                'name' => $data['name'] ?? null,
                'message' => $data['message'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]
        );

        return redirect()->route('whatsapp.templates.index')->with('success', 'Template disimpan.');
    }

    public function test(Request $request)
    {
        $request->validate([
            'event' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:32'],
        ]);
        $tenant = app('current_tenant');
        $notifier = new WhatsappNotifier($tenant);
        if (! $notifier->isConfigured()) {
            return back()->withErrors(['phone' => 'WhatsApp gateway belum aktif. Cek pengaturan Fonnte & paket Anda.']);
        }
        $sample = Customer::where('tenant_id', $tenant->id)->first();
        $rendered = $notifier->render($request->string('event')->toString(), $sample);
        if (! $rendered) {
            return back()->withErrors(['event' => 'Template untuk event tersebut tidak ada.']);
        }
        $ok = $notifier->sendRaw($request->string('phone')->toString(), $rendered, $request->string('event')->toString());

        return back()->with($ok ? 'success' : 'error', $ok ? 'Tes WA berhasil dikirim.' : 'Tes WA gagal — cek log.');
    }
}
