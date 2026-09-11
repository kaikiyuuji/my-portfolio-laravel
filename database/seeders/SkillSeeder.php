<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            [
                'seed_key' => 'computer-assembly',
                'title' => ['pt' => 'Montagem e desmontagem de computadores', 'en' => 'Computer assembly and disassembly'],
                'category' => 'technical',
            ],
            [
                'seed_key' => 'php-javascript',
                'title' => ['pt' => 'PHP e JavaScript (Básico)', 'en' => 'PHP and JavaScript (Basic)'],
                'category' => 'technical',
            ],
            [
                'seed_key' => 'laravel-eloquent',
                'title' => ['pt' => 'Laravel e Eloquent (Intermediário)', 'en' => 'Laravel and Eloquent (Intermediate)'],
                'category' => 'technical',
            ],
            [
                'seed_key' => 'composer',
                'title' => ['pt' => 'Composer (Intermediário)', 'en' => 'Composer (Intermediate)'],
                'category' => 'technical',
            ],
            [
                'seed_key' => 'mysql-relational-databases',
                'title' => ['pt' => 'MySQL e bancos de dados relacionais (Básico)', 'en' => 'MySQL and relational databases (Basic)'],
                'category' => 'technical',
            ],
            [
                'seed_key' => 'word-excel',
                'title' => ['pt' => 'Microsoft Word (Avançado) e Excel (Intermediário)', 'en' => 'Microsoft Word (Advanced) and Excel (Intermediate)'],
                'category' => 'technical',
            ],
            [
                'seed_key' => 'web-dom-oop',
                'title' => ['pt' => 'Desenvolvimento web, DOM e orientação a objetos', 'en' => 'Web development, DOM and object-oriented programming'],
                'category' => 'technical',
            ],
            [
                'seed_key' => 'cybersecurity-osint',
                'title' => ['pt' => 'Cibersegurança e OSINT', 'en' => 'Cybersecurity and OSINT'],
                'category' => 'technical',
            ],
            [
                'seed_key' => 'document-organization',
                'title' => ['pt' => 'Organização e controle de documentos', 'en' => 'Document organization and control'],
                'category' => 'interpersonal',
            ],
            [
                'seed_key' => 'customer-service',
                'title' => ['pt' => 'Atendimento ao cliente', 'en' => 'Customer service'],
                'category' => 'interpersonal',
            ],
            [
                'seed_key' => 'agile-teamwork',
                'title' => ['pt' => 'Trabalho em equipes ágeis', 'en' => 'Working in agile teams'],
                'category' => 'interpersonal',
            ],
        ];

        foreach ($skills as $index => $skill) {
            $seedKey = $skill['seed_key'];
            unset($skill['seed_key']);

            // Only the seeder can set this internal key. Existing user edits are preserved.
            Skill::unguarded(fn () => Skill::firstOrCreate(
                ['seed_key' => $seedKey],
                [...$skill, 'order' => $index, 'is_visible' => true]
            ));
        }
    }
}
