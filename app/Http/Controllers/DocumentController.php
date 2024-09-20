<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Jobs\ProcessDocument;
use App\Jobs\UploadToVectorDb;
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
        $documents = Document::where('owner_id', auth()->user()->id)->get();

        return Inertia::render('Knowledgebase', ['documents' => $documents]);
    }

    public function destroy(Document $document)
    {
        // Ensure the authenticated user owns the document
        if ($document->owner_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the file from storage
        Storage::disk('public')->delete($document->path);

        // If there's a separate thumbnail, delete it too
        if ($document->thumbnailPath && $document->thumbnailPath !== $document->path) {
            Storage::disk('public')->delete($document->thumbnailPath);
        }

        // Delete the document record from the database
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document deleted successfully');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $uploadedDocuments = [];
        $uploadErrors = [];

        foreach ($request->file('files') as $file) {
            try {
                Storage::disk('public')->putFile('documents', $file);

                $document = new Document([
                    'name' => $file->getClientOriginalName(),
                    'path' => 'documents/' . $file->hashName(),
                    'type' => $file->getClientMimeType(),
                    'owner_id' => auth()->id(),
                    'status' => 'processing',
                ]);

                $document->save();

                // Dispatch job
                ProcessDocument::dispatch($document);



                $uploadedDocuments[] = $document;
            } catch (\Exception $e) {
                Log::error('Document upload failed: ' . $e->getMessage());
                $uploadErrors[] = "Failed to upload {$file->getClientOriginalName()}: {$e->getMessage()}";
            }
        }

        if (count($uploadedDocuments) > 0) {
            $message = count($uploadedDocuments) . ' document(s) uploaded successfully.';
            if (count($uploadErrors) > 0) {
                $message .= ' Some files encountered errors.';
            }
            return redirect()->back()
                ->with('success', $message)
                ->with('uploadErrors', $uploadErrors);
        } else {
            return redirect()->back()
                ->with('error', 'No documents were uploaded successfully.')
                ->with('uploadErrors', $uploadErrors);
        }
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $document->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Document updated successfully');
    }

}