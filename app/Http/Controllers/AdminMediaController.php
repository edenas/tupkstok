<?php

namespace App\Http\Controllers;

use App\Services\MediaLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMediaController extends Controller
{
    public function index(MediaLibraryService $mediaLibrary): View
    {
        return view('admin.media-library', [
            'files' => $mediaLibrary->all(),
        ]);
    }

    public function store(Request $request, MediaLibraryService $mediaLibrary): RedirectResponse
    {
        $validated = $request->validate(
            [
                'file' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp,gif',
                    'max:5120',
                ],
            ],
            [
                'file.required' => 'Pasirinkite failą.',
                'file.image' => 'Failas turi būti paveikslėlis.',
                'file.mimes' => 'Galimi formatai: jpg, jpeg, png, webp arba gif.',
                'file.max' => 'Failas negali būti didesnis nei 5 MB.',
            ],
        );

        $mediaLibrary->store($validated['file']);

        return redirect()
            ->route('admin.media-library')
            ->with('success', 'Failas sėkmingai įkeltas.');
    }

    public function destroy(string $filename, MediaLibraryService $mediaLibrary): RedirectResponse
    {
        abort_unless($mediaLibrary->delete($filename), 404);

        return redirect()
            ->route('admin.media-library')
            ->with('success', 'Failas sėkmingai ištrintas.');
    }
}
