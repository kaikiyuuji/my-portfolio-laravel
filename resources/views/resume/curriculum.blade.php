<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Currículo - {{ $name }}</title>
    <style>
        @page { size: A4; margin: 35pt 35pt 36pt; }
        body { margin: 0; font-family: "Times-Roman", serif; font-size: 11.25pt; line-height: 1.25; color: #444; overflow-wrap: break-word; }
        h1 { margin: 0 0 6pt; font-size: 18pt; font-weight: normal; text-align: center; }
        .contact { margin: 0 0 15pt; text-align: center; font-size: 9.75pt; font-style: italic; }
        a { color: #69a6b9; text-decoration: none; }
        .email { color: #ad658e; }
        h2 { margin: 15pt 0 14pt; padding: 0 0 2pt; border-bottom: .6pt solid #000; font-size: 11.25pt; font-weight: bold; line-height: 1.15; page-break-after: avoid; }
        h3 { margin: 14pt 0 2pt; font-size: 11.25pt; font-weight: bold; page-break-after: avoid; }
        .entry-heading { width: 100%; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid; page-break-after: avoid; }
        .entry-heading td { padding: 0; vertical-align: top; overflow-wrap: break-word; }
        .entry-heading .main { width: 68%; padding-right: 8pt; }
        .entry-heading .aside { width: 32%; text-align: right; }
        .entry-heading .course { width: 57%; padding-right: 6pt; }
        .entry-heading .period { width: 43%; text-align: right; }
        .company { color: #8077a0; font-weight: bold; }
        .role { font-style: italic; }
        .entry { margin: 0 0 15pt; }
        .education-details { margin: 0; white-space: pre-line; }
        ul { margin: 0; padding: 0 0 0 35pt; }
        li { margin: 0; padding-left: 1pt; }
        .group { margin-bottom: 15pt; }
        .skill-description { white-space: pre-line; }
        p { orphans: 2; widows: 2; }
    </style>
</head>
<body>
    <h1>{{ $name }}</h1>
    <p class="contact">
        @if($settings->location){{ $settings->location }} &bull; @endif
        <a class="email" href="mailto:{{ $email }}">{{ $email }}</a>
        @if($settings->phone) &bull; {{ $settings->phone }}@endif
        @if($linkedinUrl) &bull; <a href="{{ $linkedinUrl }}">LinkedIn</a>@endif
        @if($websiteUrl) &bull; <a href="{{ $websiteUrl }}">Portfólio</a>@endif
    </p>

    @if(count($settings->education ?? []))
        <h2>EDUCAÇÃO</h2>
        @foreach($settings->education as $education)
            <div class="entry">
                <table class="entry-heading">
                    <tr>
                        <td class="main"><strong>{{ $education['institution'] }}</strong></td>
                        <td class="aside">{{ $education['location'] ?? '' }}</td>
                    </tr>
                </table>
                <table class="entry-heading">
                    <tr>
                        <td class="course">{{ $education['course'] }}</td>
                        <td class="period">{{ $education['period'] ?? '' }}</td>
                    </tr>
                </table>
                @if($education['details'] ?? '')<p class="education-details">{{ $education['details'] }}</p>@endif
            </div>
        @endforeach
    @endif

    @if($experiences->isNotEmpty())
        <h2>EXPERIÊNCIA</h2>
        @foreach($experiences as $experience)
            <div class="entry">
                <table class="entry-heading">
                    <tr>
                        <td class="main company">{{ $experience['company'] }}</td>
                        <td class="aside">{{ $experience['location'] }}</td>
                    </tr>
                </table>
                <table class="entry-heading">
                    <tr>
                        <td class="course role">{{ $experience['role'] }}</td>
                        <td class="period">{{ $experience['period'] }}</td>
                    </tr>
                </table>
                @if(count($experience['details']))
                    <ul>@foreach($experience['details'] as $detail)<li>{{ $detail }}</li>@endforeach</ul>
                @endif
            </div>
        @endforeach
    @endif

    @if($skills->isNotEmpty() || count($settings->certifications ?? []) || count($settings->languages ?? []) || count($settings->interests ?? []))
        <h2>HABILIDADES (SKILLS)</h2>
        @if(count($settings->certifications ?? []))
            <div class="group">
                <h3>Certificados:</h3>
                <ul>@foreach($settings->certifications as $certification)<li>{{ $certification }}</li>@endforeach</ul>
            </div>
        @endif
        @foreach(['technical' => 'Habilidades Técnicas:', 'interpersonal' => 'Competências Profissionais:'] as $category => $title)
            @if($skills->where('category', $category)->isNotEmpty())
                <div class="group">
                    <h3>{{ $title }}</h3>
                    <ul>
                        @foreach($skills->where('category', $category) as $skill)
                            <li><strong>{{ $skill['title'] }}</strong>@if($skill['description']): <span class="skill-description">{{ $skill['description'] }}</span>@endif</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
        @if(count($settings->languages ?? []))
            <div class="group">
                <h3>Idiomas:</h3>
                <ul>@foreach($settings->languages as $language)<li>{{ $language['name'] }}: {{ $language['level'] }}</li>@endforeach</ul>
            </div>
        @endif
        @if(count($settings->interests ?? []))
            <div class="group">
                <h3>Interesses:</h3>
                <ul>@foreach($settings->interests as $interest)<li>{{ $interest }}</li>@endforeach</ul>
            </div>
        @endif
    @endif
</body>
</html>
