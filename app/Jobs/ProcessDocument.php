<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProcessDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function handle()
    {
        Log::info('ProcessDocument job started for document: ' . $this->document->id);
        
        try {
            $response = $this->processFile($this->document);
            $result = $response->json();

            // Check if the document was successfully uploaded
            if ($response->successful() && isset($result['vector_id']) && isset($result['document_id'])) {
                // Update document with processed data
                $this->document->update([
                    'status' => 'processed',
                    'vectorID' => $result['vector_id'],
                    // 'document_id' => $result['document_id'],
                ]);
                
                Log::info('ProcessDocument job completed for document: ' . $this->document->id);
            } else {
                throw new \Exception("Invalid response from document processing: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ProcessDocument job failed: ' . $e->getMessage());
            $this->document->update(['status' => 'error']);
            throw $e;
        }
    }

    protected function processFile(Document $document)
    {
        $endpoint = 'upload-document';
        $filePath = 'documents/' . basename($document->path);
        $fullPath = storage_path('app/public/' . $filePath);
        
        if (!file_exists($fullPath)) {
            throw new \Exception("File not found: " . $fullPath);
        }

        $response = Http::withHeaders([
            'X-API-Key' => config('services.llm_api.key'),
        ])
        ->attach('file', file_get_contents($fullPath), $document->name)
        ->post(config('services.llm_api.url') . $endpoint, [
            'user_id' => $document->owner_id,
            'document_id' => $document->id
        ]);

        if (!$response->successful()) {
            throw new \Exception("API request failed: " . $response->body());
        }
        return $response;
    }

    public function failed(\Throwable $exception)
    {
        Log::error('ProcessDocument job failed after all attempts: ' . $exception->getMessage());
        $this->document->update(['status' => 'failed']);
    }

    protected function getDocumentData(Document $document, string $endpoint)
    {
        $filePath = 'documents/' . basename($document->path);
        $fullPath = storage_path('app/public/' . $filePath);

        if (!file_exists($fullPath)) {
            throw new \Exception("File not found: " . $fullPath);
        }

        $response = Http::withHeaders([
            'X-API-Key' => config('services.llm_api.key'),
        ])
        ->attach('file', file_get_contents($fullPath), $document->name)
        ->post(config('services.llm_api.url') . $endpoint);

        return $response;
    }
}
