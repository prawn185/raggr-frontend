<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\GitHubService;

class IntegrationsController extends Controller
{
    public function index()
    {
        return Inertia::render('Integrations');
    }

    public function integrateGitHub(Request $request, GitHubService $gitHubService)
    {
        $request->validate([
            'repo_url' => 'required|url',
        ]);

        try {
            $gitHubService->integrateRepository($request->repo_url);
            return back()->with('success', 'GitHub repository integrated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['repo_url' => $e->getMessage()]);
        }
    }
}
