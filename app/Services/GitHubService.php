<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GitHubService
{
    private string $token;
    private string $base = 'https://api.github.com';

    public function __construct()
    {
        $this->token = env('GITHUB_TOKEN', '');
    }

    public function parseUrl(string $url): array
    {
        $url = trim($url);
        // Accepts: https://github.com/owner/repo or owner/repo
        if (preg_match('#github\.com/([^/]+)/([^/\s]+)#', $url, $m)) {
            return ['owner' => $m[1], 'repo' => rtrim($m[2], '/')];
        }
        if (preg_match('#^([^/]+)/([^/\s]+)$#', $url, $m)) {
            return ['owner' => $m[1], 'repo' => $m[2]];
        }
        throw new \InvalidArgumentException('Invalid GitHub URL: ' . $url);
    }

    public function getRepoInfo(string $owner, string $repo): array
    {
        return $this->get("/repos/{$owner}/{$repo}");
    }

    public function getFileTree(string $owner, string $repo, string $branch = 'main'): array
    {
        try {
            $data = $this->get("/repos/{$owner}/{$repo}/git/trees/{$branch}?recursive=1");
            return $data['tree'] ?? [];
        } catch (\Exception $e) {
            // Try 'master' if 'main' fails
            if ($branch === 'main') {
                $data = $this->get("/repos/{$owner}/{$repo}/git/trees/master?recursive=1");
                return $data['tree'] ?? [];
            }
            throw $e;
        }
    }

    public function getFileContent(string $owner, string $repo, string $path): string
    {
        $data = $this->get("/repos/{$owner}/{$repo}/contents/{$path}");
        if (!isset($data['content'])) return '';
        return base64_decode(str_replace("\n", '', $data['content']));
    }

    public function selectKeyFiles(array $tree): array
    {
        $priority = [
            'README.md', 'README', 'composer.json', 'package.json',
            'Gemfile', 'requirements.txt', 'go.mod', 'Cargo.toml',
            'pyproject.toml', '.env.example', 'Dockerfile',
        ];
        $extensions = ['php', 'rb', 'py', 'go', 'ts', 'js', 'java', 'cs', 'rs'];
        $exclude = ['vendor/', 'node_modules/', '.git/', 'dist/', 'build/', 'storage/'];

        $files = array_filter($tree, fn($f) => $f['type'] === 'blob');
        $files = array_filter($files, function ($f) use ($exclude) {
            foreach ($exclude as $ex) {
                if (str_starts_with($f['path'], $ex)) return false;
            }
            return true;
        });

        $selected = [];

        // Priority files first
        foreach ($priority as $name) {
            foreach ($files as $f) {
                if (basename($f['path']) === $name) {
                    $selected[] = $f['path'];
                    break;
                }
            }
        }

        // Key source files (max 8, smaller files preferred)
        $sourceFiles = array_filter($files, function ($f) use ($extensions) {
            $ext = pathinfo($f['path'], PATHINFO_EXTENSION);
            return in_array($ext, $extensions) && ($f['size'] ?? 0) < 50000;
        });

        usort($sourceFiles, fn($a, $b) => ($a['size'] ?? 0) <=> ($b['size'] ?? 0));

        foreach (array_slice($sourceFiles, 0, 8) as $f) {
            if (!in_array($f['path'], $selected)) {
                $selected[] = $f['path'];
            }
        }

        return array_slice($selected, 0, 12);
    }

    private function get(string $path): array
    {
        $headers = [
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'CodeLens-AI/1.0',
        ];
        if ($this->token) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }

        $response = Http::withHeaders($headers)->get($this->base . $path);

        if (!$response->successful()) {
            throw new \RuntimeException("GitHub API error: {$response->status()} for {$path}");
        }

        return $response->json();
    }
}
