<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AgendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $agendas = Agenda::with(['creator', 'participants', 'resources'])
            ->latest()
            ->get();

        return Inertia::render('Agendas/Index', [
            'agendas' => $agendas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Logika untuk mengambil data pendukung (misal: daftar user, daftar ruangan)
        // $users = User::all();
        // $resources = Resource::all();

        // Tampilkan component form React
        return Inertia::render('Agendas/Create', [
            // 'users' => $users,
            // 'resources' => $resources,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi (Inertia/Laravel akan otomatis redirect-back jika gagal)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'exists:users,id',
            'resource_ids' => 'nullable|array',
            'resource_ids.*' => 'exists:resources,id',
        ]);

        // 2. Validasi Konflik Booking (Logika ini tetap sama)
        if ($request->has('resource_ids')) {
            $isConflict = $this->checkBookingConflict(
                $validated['start_time'],
                $validated['end_time'],
                $validated['resource_ids']
            );

            if ($isConflict) {
                // Kembalikan error ke form
                return back()->withErrors(['resource_ids' => 'Jadwal booking aset/ruangan bentrok!']);
            }
        }

        // 3. Buat Agenda
        $agenda = Agenda::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        // 4. Attach Relasi
        if ($request->has('participant_ids')) {
            $agenda->participants()->attach($validated['participant_ids']);
        }

        if ($request->has('resource_ids')) {
            $agenda->resources()->attach($validated['resource_ids']);
        }

        // 5. Redirect kembali ke halaman index DENGAN pesan sukses
        return to_route('agendas.index')->with('message', 'Agenda berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Agenda $agenda)
    {
        $agenda->load(['creator', 'participants', 'resources']);

        // Render component React di resources/js/Pages/Agendas/Show.jsx
        return Inertia::render('Agendas/Show', [
            'agenda' => $agenda,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agenda $agenda)
    {
        $agenda->load(['participants', 'resources']);

        // Tampilkan component form React (bisa pakai ulang component Create)
        return Inertia::render('Agendas/Edit', [
            'agenda' => $agenda,
            // 'users' => User::all(),
            // 'resources' => Resource::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agenda $agenda)
    {
        // 1. Validasi
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'exists:users,id',
            'resource_ids' => 'nullable|array',
            'resource_ids.*' => 'exists:resources,id',
        ]);

        // 2. Validasi Konflik Booking (Logika tetap sama)
        if ($request->has('resource_ids')) {
            $isConflict = $this->checkBookingConflict(
                $validated['start_time'] ?? $agenda->start_time,
                $validated['end_time'] ?? $agenda->end_time,
                $validated['resource_ids'],
                $agenda->id // <-- Pengecualian
            );

            if ($isConflict) {
                return back()->withErrors(['resource_ids' => 'Jadwal booking aset/ruangan bentrok!']);
            }
        }

        // 3. Update Agenda
        $agenda->update($validated);

        // 4. Sync Relasi
        if ($request->has('participant_ids')) {
            $agenda->participants()->sync($validated['participant_ids']);
        }

        if ($request->has('resource_ids')) {
            $agenda->resources()->sync($validated['resource_ids']);
        }

        // 5. Redirect
        return to_route('agendas.index')->with('message', 'Agenda berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agenda $agenda)
    {
        $agenda->delete();

        // Redirect
        return to_route('agendas.index')->with('message', 'Agenda berhasil dihapus.');
    }

    // Fungsi Helper untuk Mengecek Konflik Booking
    private function checkBookingConflict($startTime, $endTime, $resourceIds, $excludeAgendaId = null)
    {
        // ... (Logika dari controller sebelumnya, tidak perlu diubah) ...
        $query = DB::table('agenda_resource')
            ->join('agendas', 'agenda_resource.agenda_id', '=', 'agendas.id')
            ->whereIn('agenda_resource.resource_id', $resourceIds)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($q2) use ($startTime) {
                    $q2->where('agendas.start_time', '<=', $startTime)
                       ->where('agendas.end_time', '>', $startTime);
                })
                ->orWhere(function ($q2) use ($endTime) {
                    $q2->where('agendas.start_time', '<', $endTime)
                       ->where('agendas.end_time', '>=', $endTime);
                })
                ->orWhere(function ($q2) use ($startTime, $endTime) {
                    $q2->where('agendas.start_time', '>=', $startTime)
                       ->where('agendas.end_time', '<=', $endTime);
                });
            });

        if ($excludeAgendaId) {
            $query->where('agendas.id', '!=', $excludeAgendaId);
        }

        return $query->exists();
    }
}
