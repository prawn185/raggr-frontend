<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Jobs\ProcessDocument;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documents = Document::where('owner_id', auth()->id())->latest()->get();

        return Inertia::render('Knowledgebase', ['documents' => $documents]);
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        Storage::disk('public')->delete($document->path);
        if ($document->thumbnailPath && $document->thumbnailPath !== $document->path) {
            Storage::disk('public')->delete($document->thumbnailPath);
        }

        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,txt|max:10240', // Increased max size to 10MB
        ]);

        $uploadedDocuments = [];

        foreach ($request->file('files') as $file) {
            $path = Storage::disk('public')->putFile('documents', $file);

            $document = Document::create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getClientMimeType(),
                'owner_id' => auth()->id(),
                'status' => 'processing',
                'content' => mb_convert_encoding(Storage::disk('public')->get($path), 'UTF-8', 'UTF-8'),
            ]);

            $uploadedDocuments[] = $document;

            ProcessDocument::dispatch($document);
        }

        return redirect()->back()->with('success', count($uploadedDocuments) . ' document(s) uploaded successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $document->update($request->only('name'));

        return redirect()->back()->with('success', 'Document updated successfully');
    }

}