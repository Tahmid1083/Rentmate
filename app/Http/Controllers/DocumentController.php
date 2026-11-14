<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file'        => ['required', 'file', 'max:15360'], // 15 MB
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('file');

        // Stored on the PRIVATE disk so only the landlord can download
        // through the controller — not directly via a URL.
        $path = $file->store('documents', 'private');

        $document = $request->user()->documents()->create([
            'name'        => $file->getClientOriginalName(),
            'disk'        => 'private',
            'path'        => $path,
            'mime_type'   => $file->getMimeType(),
            'size'        => $file->getSize(),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document uploaded.');
    }

    public function show(Request $request, Document $document)
    {
        $this->authorizeOwner($request, $document);

        return view('documents.show', compact('document'));
    }

    public function download(Request $request, Document $document)
    {
        $this->authorizeOwner($request, $document);

        return $document->streamDownload();
    }

    public function destroy(Request $request, Document $document)
    {
        $this->authorizeOwner($request, $document);

        Storage::disk($document->disk)->delete($document->path);

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted.');
    }

    protected function authorizeOwner(Request $request, Document $document): void
    {
        if ($document->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
