<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('reviews')->truncate();

        $repos = [
            // 1位 👑 Hall of Fame — overall 91 (S)
            [
                'owner' => 'tailwindlabs', 'repo' => 'tailwindcss',
                'language' => 'JavaScript', 'framework' => 'Tailwind CSS',
                'quality_score' => 92, 'security_score' => 90, 'maintainability_score' => 91,
                'view_count' => 45,
            ],
            // 2位 🚀 Rising Star — overall 82 (A)
            [
                'owner' => 'vitejs', 'repo' => 'vite',
                'language' => 'TypeScript', 'framework' => 'Vite',
                'quality_score' => 82, 'security_score' => 80, 'maintainability_score' => 84,
                'view_count' => 38,
            ],
            // 3位 🔥 Fan Favorite — overall 79 (B)
            [
                'owner' => 'laravel', 'repo' => 'laravel',
                'language' => 'PHP', 'framework' => 'Laravel',
                'quality_score' => 78, 'security_score' => 80, 'maintainability_score' => 79,
                'view_count' => 32,
            ],
            // 4位 🛡 Security Master — secScore 88 ≥ 85, overall 85 (A)
            [
                'owner' => 'denoland', 'repo' => 'deno',
                'language' => 'Rust', 'framework' => 'Deno',
                'quality_score' => 83, 'security_score' => 88, 'maintainability_score' => 84,
                'view_count' => 26,
            ],
            // 5位 💎 Clean Code — qlScore 87 ≥ 85, overall 85 (A)
            [
                'owner' => 'django', 'repo' => 'django',
                'language' => 'Python', 'framework' => 'Django',
                'quality_score' => 87, 'security_score' => 83, 'maintainability_score' => 85,
                'view_count' => 20,
            ],
            // 6位 ⚡ Top Rated — overall 85 (A), sec/ql どちらも < 85
            [
                'owner' => 'facebook', 'repo' => 'react',
                'language' => 'JavaScript', 'framework' => 'React',
                'quality_score' => 83, 'security_score' => 82, 'maintainability_score' => 91,
                'view_count' => 15,
            ],
            // 7位 👀 Trending — overall 74 (B)
            [
                'owner' => 'sveltejs', 'repo' => 'svelte',
                'language' => 'TypeScript', 'framework' => 'Svelte',
                'quality_score' => 75, 'security_score' => 73, 'maintainability_score' => 74,
                'view_count' => 14,
            ],
            // 8位 👀 Trending — overall 70 (B)
            [
                'owner' => 'nuxt', 'repo' => 'nuxt',
                'language' => 'TypeScript', 'framework' => 'Nuxt',
                'quality_score' => 70, 'security_score' => 68, 'maintainability_score' => 72,
                'view_count' => 11,
            ],
            // 9位 — overall 65 (C)
            [
                'owner' => 'remix-run', 'repo' => 'remix',
                'language' => 'TypeScript', 'framework' => 'Remix',
                'quality_score' => 65, 'security_score' => 63, 'maintainability_score' => 67,
                'view_count' => 7,
            ],
            // 10位 — overall 60 (C)
            [
                'owner' => 'expressjs', 'repo' => 'express',
                'language' => 'JavaScript', 'framework' => 'Express',
                'quality_score' => 60, 'security_score' => 58, 'maintainability_score' => 61,
                'view_count' => 4,
            ],
        ];

        foreach ($repos as $r) {
            DB::table('reviews')->insert([
                'github_url'             => "https://github.com/{$r['owner']}/{$r['repo']}",
                'owner'                  => $r['owner'],
                'repo'                   => $r['repo'],
                'branch'                 => 'main',
                'language'               => $r['language'],
                'quality_score'          => $r['quality_score'],
                'security_score'         => $r['security_score'],
                'maintainability_score'  => $r['maintainability_score'],
                'review_data'            => json_encode([
                    'language'  => $r['language'],
                    'framework' => $r['framework'],
                ]),
                'status'                 => 'complete',
                'ip_hash'                => null,
                'view_count'             => $r['view_count'],
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }
}
