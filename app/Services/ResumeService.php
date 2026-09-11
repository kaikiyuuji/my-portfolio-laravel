<?php

namespace App\Services;

use App\Models\Experience;
use App\Models\ResumeSetting;
use App\Models\Skill;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;

class ResumeService
{
    public function __construct(private ProfileService $profileService) {}

    public function settings(): ResumeSetting
    {
        return ResumeSetting::firstOrCreate(['id' => 1], [
            'education' => [], 'certifications' => [], 'languages' => [], 'interests' => [],
        ]);
    }

    public function updateSettings(array $data): ResumeSetting
    {
        $settings = $this->settings();
        $settings->update($data);

        return $settings->fresh();
    }

    public function data(): array
    {
        $profile = $this->profileService->get();
        $settings = $this->settings();

        return [
            'name' => $profile->name,
            'email' => $profile->email,
            'settings' => $settings,
            'linkedinUrl' => $this->httpUrl($settings->linkedin_url),
            'websiteUrl' => $this->httpUrl($settings->website_url ?: url('/')),
            'experiences' => Experience::ordered()->orderByDesc('id')->get()->map(fn ($experience) => [
                'company' => $this->portuguese($experience->getTranslations('company')),
                'role' => $this->portuguese($experience->getTranslations('role')),
                'location' => $experience->location,
                'period' => $experience->start_date->locale('pt_BR')->translatedFormat('F Y')
                    .' - '.($experience->end_date?->locale('pt_BR')->translatedFormat('F Y') ?? 'Atual'),
                'details' => $this->lines($this->portuguese($experience->getTranslations('description'))),
            ]),
            'skills' => Skill::visible()->ordered()->get()->map(fn ($skill) => [
                'title' => $this->portuguese($skill->getTranslations('title')),
                'description' => $this->portuguese($skill->getTranslations('description')),
                'category' => $skill->category,
            ]),
        ];
    }

    public function html(array $data): string
    {
        return view('resume.curriculum', $data)->render();
    }

    public function pdf(array $data): string
    {
        $options = new Options;
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);
        $options->setIsJavascriptEnabled(false);
        $options->setChroot(resource_path('views/resume'));
        $options->setDefaultFont('Times-Roman');
        $pdf = new Dompdf($options);
        $pdf->setPaper('A4');
        $pdf->loadHtml($this->html($data), 'UTF-8');
        $pdf->render();
        $pdf->addInfo('Title', 'Currículo - '.$data['name']);
        $pdf->addInfo('Author', $data['name']);

        return $pdf->output();
    }

    private function portuguese(array $translations): string
    {
        return ($translations['pt'] ?? '') ?: (($translations['pt_BR'] ?? '') ?: ($translations['en'] ?? ''));
    }

    private function httpUrl(?string $url): ?string
    {
        return $url && Str::isUrl($url, ['http', 'https']) ? $url : null;
    }

    private function lines(string $text): array
    {
        return array_values(array_filter(array_map(
            fn ($line) => preg_replace('/^\s*[•*\-]\s*/u', '', trim($line)),
            preg_split('/\R/u', $text) ?: [],
        ), fn ($line) => $line !== ''));
    }
}
