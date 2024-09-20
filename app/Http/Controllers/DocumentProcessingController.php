<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentProcessingController extends Controller
{

    public function getDocumentData(Document $document): Response
    {
        if ($document->type === 'application/pdf') {
            return $this->httpRequest($document, 'process-pdf');
        } elseif (in_array($document->type, ['image/jpeg', 'image/png', 'image/jpg'])) {
            return $this->httpRequest($document, 'process-img');
        } else {
            throw new \Exception("Unsupported file type: " . $document->type);
        }
    }

    public function httpRequest(Document $document, string $endpoint): Response
    {
        $filePath = Storage::url($document->rel_path);
        $response = Http::withHeaders([
            'X-API-Key' => config('services.llm_api.key'),
        ])
        ->withBody(file_get_contents($filePath), $document->type)
        ->post(config('services.llm_api.url') . $endpoint);

        return $response;
    }
}