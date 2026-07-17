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

    public function getRepoStats(string $owner, string $repo): array
    {
        $info = $this->getRepoInfo($owner, $repo);

        return [
            'stars'       => $info['stargazers_count'] ?? 0,
            'forks'       => $info['forks_count'] ?? 0,
            'watchers'    => $info['watchers_count'] ?? 0,
            'language'    => $info['language'] ?? null,
            'topics'      => $info['topics'] ?? [],
            'description' => $info['description'] ?? '',
            'commits'     => $this->getCommitCount($owner, $repo),
            'open_issues' => $info['open_issues_count'] ?? 0,
            'default_branch' => $info['default_branch'] ?? 'main',
        ];
    }

    public function getFileTree(string $owner, string $repo, string $branch = 'main'): array
    {
        try {
            $data = $this->get("/repos/{$owner}/{$repo}/git/trees/{$branch}?recursive=1");
            return $data['tree'] ?? [];
        } catch (\Exception $e) {
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
        foreach ($priority as $name) {
            foreach ($files as $f) {
                if (basename($f['path']) === $name) {
                    $selected[] = $f['path'];
                    break;
                }
            }
        }

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

    private function getCommitCount(string $owner, string $repo): int
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->base}/repos/{$owner}/{$repo}/commits?per_page=1");
            $link = $response->header('Link', '');
            if (preg_match('/page=(\d+)>;\s*rel="last"/', $link, $m)) {
                return (int) $m[1];
            }
            return count($response->json() ?? []) > 0 ? 1 : 0;
        } catch (\Exception) {
            return 0;
        }
    }

    private function get(string $path): array
    {
        $response = Http::withHeaders($this->headers())->get($this->base . $path);
        if (!$response->successful()) {
            throw new \RuntimeException("GitHub API error: {$response->status()} for {$path}");
        }
        return $response->json();
    }

    private function headers(): array
    {
        $h = [
            'Accept'     => 'application/vnd.github.v3+json',
            'User-Agent' => 'DevInsight-AI/1.0',
        ];
        if ($this->token) $h['Authorization'] = "Bearer {$this->token}";
        return $h;
    }
}
