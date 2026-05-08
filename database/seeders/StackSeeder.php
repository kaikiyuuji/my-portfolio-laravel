<?php

namespace Database\Seeders;

use App\Models\Stack;
use Illuminate\Database\Seeder;

class StackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stacks = [
            [
                'name' => 'PHP',
                'icon_slug' => 'php',
                'color' => '#777BB4',
                'is_featured' => true,
            ],
            [
                'name' => 'Laravel',
                'icon_slug' => 'laravel',
                'color' => '#FF2D20',
                'is_featured' => true,
            ],
            [
                'name' => 'JavaScript',
                'icon_slug' => 'javascript',
                'color' => '#F7DF1E',
                'is_featured' => true,
            ],
            [
                'name' => 'Node.js',
                'icon_slug' => 'nodedotjs',
                'color' => '#5FA04E',
                'is_featured' => true,
            ],
            [
                'name' => 'React',
                'icon_slug' => 'react',
                'color' => '#61DAFB',
                'is_featured' => true,
            ],
            [
                'name' => 'Vue.js',
                'icon_slug' => 'vuedotjs',
                'color' => '#4FC08D',
                'is_featured' => true,
            ],
            [
                'name' => 'Vite',
                'icon_slug' => 'vite',
                'color' => '#646CFF',
                'is_featured' => false,
            ],
            [
                'name' => 'Docker',
                'icon_slug' => 'docker',
                'color' => '#2496ED',
                'is_featured' => true,
            ],
            [
                'name' => 'Git',
                'icon_slug' => 'git',
                'color' => '#F05032',
                'is_featured' => false,
            ],
            [
                'name' => 'GitHub',
                'icon_slug' => 'github',
                'color' => '#181717',
                'is_featured' => false,
            ],
            [
                'name' => 'Jira',
                'icon_slug' => 'jira',
                'color' => '#0052CC',
                'is_featured' => false,
            ],
            [
                'name' => 'Bitbucket',
                'icon_slug' => 'bitbucket',
                'color' => '#0052CC',
                'is_featured' => false,
            ],
        ];

        foreach ($stacks as $index => $stack) {
            Stack::updateOrCreate(
                ['icon_slug' => $stack['icon_slug']],
                array_merge($stack, ['order' => $index])
            );
        }
    }
}
