<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Document;

class GitHubService
{
    public function integrateRepository($repoUrl)
    {
        // Extract owner and repo name from the URL
        preg_match('/github\.com\/([^\/]+)\/([^\/]+)/', $repoUrl, $matches);
        $owner = $matches[1];
        $repo = $matches[2];

        // Fetch repository contents
        $response = Http::get("https://api.github.com/repos/{$owner}/{$repo}/contents");
        $contents = $response->json();

        foreach ($contents as $item) {
            if ($item['type'] === 'file') {
                $this->processFile($item, $owner, $repo);
            }
        }
    }

    private function processFile($file, $owner, $repo)
    {
        $content = Http::get($file['download_url'])->body();

        Document::create([
            'name' => $file['name'],
            'content' => $content,
            'source' => "github:{$owner}/{$repo}",
            'type' => $this->getFileType($file['name']),
        ]);
    }

    private function getFileType($fileName)
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        // Map extensions to document types
        $typeMap = [
            'md' => 'markdown',
            'txt' => 'text',
            'py' => 'python',
            'js' => 'javascript',
            // Add more mappings as needed
        ];
        return $typeMap[$extension] ?? 'unknown';
    }
}