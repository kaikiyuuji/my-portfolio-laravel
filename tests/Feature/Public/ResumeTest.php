<?php

namespace Tests\Feature\Public;

use App\Models\Experience;
use App\Models\Profile;
use App\Models\Skill;
use App\Services\ResumeService;
use ErrorException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeTest extends TestCase
{
    use RefreshDatabase;

    private function profile(array $overrides = []): Profile
    {
        return Profile::create(array_replace([
            'name' => 'Ana Maria Silva',
            'email' => 'ana@example.com',
            'headline' => ['pt' => 'Engenheira de software', 'en' => 'Software engineer'],
            'bio' => ['pt' => 'Desenvolvimento de sistemas.', 'en' => 'Software development.'],
        ], $overrides));
    }

    private function experience(array $overrides = []): Experience
    {
        return Experience::create(array_replace([
            'company' => ['pt' => 'Empresa Brasileira', 'en' => 'Brazilian Company'],
            'role' => ['pt' => 'Desenvolvedora', 'en' => 'Developer'],
            'description' => ['pt' => "- Manutenção de sistemas\n• Documentação técnica", 'en' => 'System maintenance'],
            'location' => 'São Paulo, SP',
            'start_date' => '2024-01-01',
            'end_date' => null,
            'order' => 0,
        ], $overrides));
    }

    private function skill(array $overrides = []): Skill
    {
        return Skill::create(array_replace([
            'title' => ['pt' => 'Resolução de problemas', 'en' => 'Problem solving'],
            'description' => ['pt' => 'Diagnóstico e correção de falhas.', 'en' => 'Troubleshooting.'],
            'category' => 'technical',
            'order' => 0,
            'is_visible' => true,
        ], $overrides));
    }

    public function test_public_download_returns_a_real_pdf_using_the_current_profile_name(): void
    {
        $profile = $this->profile(['name' => 'Nome Antigo']);
        $profile->update(['name' => 'Ana Maria Silva']);

        $response = $this->get(route('resume.download'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="curriculo-ana-maria-silva.pdf"')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('%%EOF', $response->getContent());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('Ana Maria Silva', app(ResumeService::class)->html(app(ResumeService::class)->data()));
    }

    public function test_resume_uses_current_experience_and_visible_skills_in_portuguese_even_when_app_locale_is_english(): void
    {
        app()->setLocale('en');
        $profile = $this->profile();
        $experience = $this->experience();
        $skill = $this->skill(['order' => 5]);
        $this->skill(['title' => ['pt' => 'Competência oculta'], 'is_visible' => false]);
        $service = app(ResumeService::class);
        $original = $service->data();
        $this->assertSame('Desenvolvedora', $original['experiences']->first()['role']);

        $profile->update(['name' => 'Ana Atualizada']);
        $experience->update([
            'role' => ['pt' => 'Engenheira de software', 'en' => 'Software engineer'],
            'description' => ['pt' => "- Arquitetura de sistemas\n\n• Mentoria técnica", 'en' => 'System architecture'],
        ]);
        $skill->update(['title' => ['pt' => 'Análise de sistemas', 'en' => 'Systems analysis']]);
        $this->skill(['title' => ['pt' => 'Comunicação'], 'category' => 'interpersonal', 'order' => 1]);

        $data = $service->data();
        $this->assertSame('Ana Atualizada', $data['name']);
        $this->assertSame('Empresa Brasileira', $data['experiences']->first()['company']);
        $this->assertSame('Engenheira de software', $data['experiences']->first()['role']);
        $this->assertSame('janeiro 2024 - Atual', $data['experiences']->first()['period']);
        $this->assertSame(['Arquitetura de sistemas', 'Mentoria técnica'], $data['experiences']->first()['details']);
        $this->assertSame(['Comunicação', 'Análise de sistemas'], $data['skills']->pluck('title')->all());

        $html = $service->html($data);
        foreach (['Ana Atualizada', 'Engenheira de software', 'Arquitetura de sistemas', 'Análise de sistemas', 'Comunicação'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        foreach (['Competência oculta', 'Systems analysis', 'Software engineer', 'Manutenção de sistemas'] as $text) {
            $this->assertStringNotContainsString($text, $html);
        }
    }

    public function test_legacy_records_without_portuguese_or_description_translations_have_safe_fallbacks(): void
    {
        $this->profile();
        $this->experience([
            'company' => ['en' => 'Legacy Company'],
            'role' => ['pt_BR' => 'Função legada'],
            'description' => [],
        ]);
        $this->skill(['title' => ['en' => 'Legacy Skill'], 'description' => []]);

        set_error_handler(function (int $severity, string $message, string $file, int $line): never {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        try {
            $data = app(ResumeService::class)->data();
        } finally {
            restore_error_handler();
        }

        $this->assertSame('Legacy Company', $data['experiences']->first()['company']);
        $this->assertSame('Função legada', $data['experiences']->first()['role']);
        $this->assertSame([], $data['experiences']->first()['details']);
        $this->assertSame('Legacy Skill', $data['skills']->first()['title']);
        $this->assertSame('', $data['skills']->first()['description']);
    }

    public function test_resume_html_escapes_saved_content_and_excludes_unsafe_legacy_links(): void
    {
        $this->profile(['name' => '<script>alert("name")</script>']);
        $this->experience(['description' => ['pt' => '<img src=x onerror=alert(1)>']]);
        $this->skill(['title' => ['pt' => '<b>Competência</b>']]);
        $service = app(ResumeService::class);
        $service->updateSettings([
            'location' => '<strong>Cidade</strong>',
            'linkedin_url' => 'javascript:alert(1)',
            'website_url' => 'file:///etc/passwd',
            'education' => [[
                'institution' => '<script>school()</script>',
                'course' => 'Curso seguro',
                'location' => 'São Paulo',
                'period' => '2020 - 2024',
                'details' => '<img src=x onerror=school()>',
            ]],
            'certifications' => ['<script>certificate()</script>'],
            'languages' => [['name' => '<b>Inglês</b>', 'level' => 'Avançado']],
            'interests' => ['<script>interest()</script>'],
        ]);

        $data = $service->data();
        $this->assertNull($data['linkedinUrl']);
        $this->assertNull($data['websiteUrl']);
        $html = $service->html($data);

        foreach (['<script>alert("name")</script>', '<script>school()</script>', '<script>certificate()</script>', '<script>interest()</script>', '<b>Competência</b>'] as $text) {
            $this->assertStringNotContainsString($text, $html);
            $this->assertStringContainsString(e($text), $html);
        }
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString(e('<img src=x onerror=alert(1)>'), $html);
        $this->assertStringNotContainsString('href="javascript:', $html);
        $this->assertStringNotContainsString('href="file:', $html);
    }
}
