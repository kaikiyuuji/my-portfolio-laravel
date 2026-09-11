<?php

namespace Tests\Feature\Admin;

use App\Models\ResumeSetting;
use App\Models\User;
use App\Services\ResumeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ResumeTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'location' => 'São Paulo, SP',
            'phone' => '+55 11 99999-9999',
            'linkedin_url' => 'https://www.linkedin.com/in/example',
            'website_url' => 'https://portfolio.example.com',
            'education' => [[
                'institution' => 'Universidade Exemplo',
                'location' => 'São Paulo',
                'course' => 'Engenharia de Software',
                'period' => '2022 - 2026',
                'details' => "Projeto de pesquisa\nMonitoria de algoritmos",
            ]],
            'certifications' => ['Certificação em redes'],
            'languages' => [['name' => 'Inglês', 'level' => 'Avançado']],
            'interests' => ['Literatura e tecnologia'],
        ], $overrides);
    }

    public function test_resume_administration_requires_authentication(): void
    {
        $this->get(route('admin.resume.edit'))->assertRedirect('/login');
        $this->put(route('admin.resume.update'), $this->payload())->assertRedirect('/login');

        $this->assertDatabaseCount('resume_settings', 0);
    }

    public function test_admin_edits_one_settings_record_and_receives_success_feedback(): void
    {
        $this->actingAs(User::factory()->create());

        for ($visit = 0; $visit < 2; $visit++) {
            $this->get(route('admin.resume.edit'))->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Resume/Edit')
                ->where('settings.id', 1)
                ->where('settings.education', [])
            );
        }

        $this->put(route('admin.resume.update'), $this->payload(['id' => 99]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Dados do currículo atualizados.')
            ->assertRedirect(route('admin.resume.edit'));

        $settings = ResumeSetting::sole();
        $this->assertSame(1, $settings->id);
        foreach ($this->payload() as $field => $value) {
            $this->assertSame($value, $settings->{$field});
        }

        $this->get(route('admin.resume.edit'))->assertInertia(fn (Assert $page) => $page
            ->where('settings.education.0.course', 'Engenharia de Software')
            ->where('flash.success', 'Dados do currículo atualizados.')
        );

        $this->put(route('admin.resume.update'), $this->payload(['location' => 'Curitiba, PR']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('resume_settings', 1);
        $this->assertSame('Curitiba, PR', $settings->fresh()->location);
    }

    public function test_empty_collections_and_optional_fields_clear_previous_values(): void
    {
        app(ResumeService::class)->updateSettings($this->payload());

        $this->actingAs(User::factory()->create())->put(route('admin.resume.update'), [
            'location' => '', 'phone' => '', 'linkedin_url' => '', 'website_url' => '',
            'education' => [], 'certifications' => [], 'languages' => [], 'interests' => [],
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.resume.edit'));

        $settings = ResumeSetting::sole();
        foreach (['education', 'certifications', 'languages', 'interests'] as $field) {
            $this->assertSame([], $settings->{$field});
        }
        foreach (['location', 'phone', 'linkedin_url', 'website_url'] as $field) {
            $this->assertNull($settings->{$field});
        }
    }

    public function test_missing_collections_are_rejected_without_clearing_saved_data(): void
    {
        $settings = app(ResumeService::class)->updateSettings($this->payload());

        $this->actingAs(User::factory()->create())->put(route('admin.resume.update'), ['location' => 'Outro lugar'])
            ->assertSessionHasErrors(['education', 'certifications', 'languages', 'interests']);

        $this->assertSame($this->payload()['education'], $settings->fresh()->education);
        $this->assertSame('São Paulo, SP', $settings->fresh()->location);
    }

    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected_without_changing_saved_content(array $overrides, array $errors): void
    {
        $settings = app(ResumeService::class)->updateSettings($this->payload());
        $original = $settings->toArray();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.resume.update'), $this->payload($overrides))
            ->assertSessionHasErrors($errors);

        $this->assertSame($original, $settings->fresh()->toArray());
        $this->assertDatabaseCount('resume_settings', 1);
    }

    public static function invalidSettings(): array
    {
        return [
            'contact limits' => [
                ['location' => str_repeat('a', 256), 'phone' => str_repeat('1', 51)],
                ['location', 'phone'],
            ],
            'collection limits' => [
                [
                    'education' => array_fill(0, 21, ['institution' => 'School', 'course' => 'Course']),
                    'certifications' => array_fill(0, 51, 'Certificate'),
                    'languages' => array_fill(0, 21, ['name' => 'English', 'level' => 'Fluent']),
                    'interests' => array_fill(0, 31, 'Reading'),
                ],
                ['education', 'certifications', 'languages', 'interests'],
            ],
            'nested text limits' => [
                [
                    'education' => [['institution' => str_repeat('a', 256), 'course' => 'Course', 'details' => str_repeat('a', 3001)]],
                    'certifications' => [str_repeat('a', 256)],
                    'languages' => [['name' => str_repeat('a', 101), 'level' => str_repeat('a', 101)]],
                    'interests' => [str_repeat('a', 1001)],
                ],
                ['education.0.institution', 'education.0.details', 'certifications.0', 'languages.0.name', 'languages.0.level', 'interests.0'],
            ],
            'required nested content' => [
                ['education' => [['institution' => '', 'course' => '']], 'languages' => [['name' => '', 'level' => '']], 'certifications' => [''], 'interests' => ['']],
                ['education.0.institution', 'education.0.course', 'languages.0.name', 'languages.0.level', 'certifications.0', 'interests.0'],
            ],
            'unknown nested keys' => [
                [
                    'education' => [['institution' => 'School', 'course' => 'Course', 'internal_notes' => 'Must not be stored']],
                    'languages' => [['name' => 'English', 'level' => 'Fluent', 'secret' => 'Must not be stored']],
                ],
                ['education.0', 'languages.0'],
            ],
            'associative collections' => [
                [
                    'education' => ['custom' => ['institution' => 'School', 'course' => 'Course']],
                    'certifications' => ['custom' => 'Certificate'],
                    'languages' => ['custom' => ['name' => 'English', 'level' => 'Fluent']],
                    'interests' => ['custom' => 'Reading'],
                ],
                ['education', 'certifications', 'languages', 'interests'],
            ],
        ];
    }

    #[DataProvider('disallowedUrls')]
    public function test_links_only_accept_http_or_https(string $url): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('admin.resume.update'), $this->payload(['linkedin_url' => $url, 'website_url' => $url]))
            ->assertSessionHasErrors(['linkedin_url', 'website_url']);

        $this->assertDatabaseCount('resume_settings', 0);
    }

    public static function disallowedUrls(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'data' => ['data:text/html,<script>alert(1)</script>'],
            'local file' => ['file:///etc/passwd'],
            'ftp' => ['ftp://example.com/resume'],
        ];
    }
}
