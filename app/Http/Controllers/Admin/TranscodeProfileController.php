<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Security\Models\TranscodeProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TranscodeProfileController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/TranscodeProfiles/Index', [
            'profiles' => TranscodeProfile::orderBy('name')->paginate(25)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create() { return Inertia::render('Admin/TranscodeProfiles/Create'); }

    public function store(Request $request)
    {
        TranscodeProfile::create($request->validate([
            'name' => 'required|string|max:255', 'video_codec' => 'required|string',
            'audio_codec' => 'required|string', 'video_bitrate' => 'nullable|string',
            'audio_bitrate' => 'nullable|string', 'resolution' => 'nullable|string',
            'fps' => 'nullable|integer', 'preset' => 'nullable|string',
        ]));
        return redirect()->route('admin.transcode-profiles.index')->with('success', 'Profile created.');
    }

    public function edit(TranscodeProfile $transcode_profile)
    {
        return Inertia::render('Admin/TranscodeProfiles/Edit', ['profile' => $transcode_profile]);
    }

    public function update(Request $request, TranscodeProfile $transcode_profile)
    {
        $transcode_profile->update($request->validate([
            'name' => 'required|string|max:255', 'video_codec' => 'required|string',
            'audio_codec' => 'required|string', 'video_bitrate' => 'nullable|string',
            'audio_bitrate' => 'nullable|string', 'resolution' => 'nullable|string',
            'fps' => 'nullable|integer', 'preset' => 'nullable|string',
        ]));
        return redirect()->route('admin.transcode-profiles.index')->with('success', 'Profile updated.');
    }

    public function destroy(TranscodeProfile $transcode_profile)
    {
        $transcode_profile->delete();
        return redirect()->route('admin.transcode-profiles.index')->with('success', 'Profile deleted.');
    }
}
