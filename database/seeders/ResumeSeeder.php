<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\Profile;
use App\Models\ResumeSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Initial content transcribed from the owner's supplied résumé. Run explicitly. */
class ResumeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // The settings row also marks this one-time import. Re-running preserves edits/deletions.
            if (ResumeSetting::whereKey(1)->exists()) {
                return;
            }

            ResumeSetting::create([
                'id' => 1,
                'location' => 'Belém, PA',
                'phone' => '(91) 98495-3816',
                'linkedin_url' => 'https://www.linkedin.com/in/kaiki-yuuji/',
                'website_url' => 'https://kaiki-yuuji-portfolio.vercel.app/',
                'education' => [
                    [
                        'institution' => 'Universidade Federal Rural da Amazônia (UFRA)',
                        'location' => 'Belém, PA',
                        'course' => 'Graduação em Licenciatura da Computação (Não Finalizado)',
                        'period' => 'De: 08/2022 Até: 06/2024',
                        'details' => 'Programação Orientada a Objetos, Algoritmos & Estruturas de Dados, Desenvolvimento Web, Bancos de Dados & SQL, Engenharia de Software, Redes de Computadores, Sistemas Operacionais.',
                    ],
                    [
                        'institution' => 'Universidade Estácio de Sá',
                        'location' => 'Belém, PA',
                        'course' => 'Graduação em Ciência da Computação',
                        'period' => 'De: 02/2025 Até: Andamento (Previsão 2/2028)',
                        'details' => 'Segurança da Informação, Computação em Nuvem, Bancos de Dados & SQL, Inteligência Artificial & Machine Learning, Pensamento Computacional.',
                    ],
                ],
                'certifications' => [
                    'Fundamentos da Programação com PHP', 'Curso JavaScript Básico',
                    'Curso PHP Composer', 'Curso Laravel - Banco de Dados Relacional', 'Curso Introdução ao Laravel 8',
                ],
                'languages' => [
                    ['name' => 'Espanhol', 'level' => 'Básico'],
                    ['name' => 'Inglês', 'level' => 'Básico'],
                ],
                'interests' => [
                    'Aperfeiçoamento de processos administrativos e aprimoramento da eficiência organizacional.',
                    'Interesse em novas tecnologias e ferramentas que melhorem a gestão de documentos e a automação de tarefas.',
                    'Desenvolvimento de soluções inovadoras para otimizar a experiência do cliente e facilitar operações diárias.',
                    'Acompanhamento de tendências em tecnologia, especialmente na área de segurança da informação e sistemas de gestão.',
                ],
            ]);

            $profile = Profile::firstOrCreate([], [
                'name' => 'Kaiki Yuuji H. Ramos', 'email' => 'kaikiramoshirata@gmail.com',
                'headline' => ['pt' => 'Desenvolvimento Web', 'en' => 'Web Development'], 'bio' => [],
            ]);
            if ($profile->name === 'Your Name' && $profile->email === 'email@example.com') {
                $profile->update([
                    'name' => 'Kaiki Yuuji H. Ramos', 'email' => 'kaikiramoshirata@gmail.com',
                    'headline' => ['pt' => 'Desenvolvimento Web', 'en' => 'Web Development'],
                ]);
            }

            if (Experience::exists()) {
                return;
            }

            $experiences = [
                [
                    'company' => ['pt' => 'DECCC - Polícia Civil do Estado do Pará'],
                    'role' => ['pt' => 'Estagiário em Cybersegurança'],
                    'start_date' => '2022-08-01', 'end_date' => '2023-04-01',
                    'description' => ['pt' => "Realização de operações de inteligência de fontes abertas (OSINT), utilizando diversas ferramentas para coleta e análise de dados estratégicos.\nApoio administrativo no gerenciamento de documentos e dados, utilizando Microsoft Word e Excel para organização e processamento de informações.\nColaboração na elaboração de relatórios e na comunicação de dados importantes para a equipe de investigação."],
                ],
                [
                    'company' => ['pt' => 'Kolares TI - Engenharia de Software Ágil'],
                    'role' => ['pt' => 'Estagiário em Desenvolvimento de Sistemas Web'],
                    'start_date' => '2023-05-01', 'end_date' => '2024-02-01',
                    'description' => ['pt' => "Desenvolvimento de sistemas web utilizando PHP e o framework Laravel, aprimorando a performance e escalabilidade das aplicações.\nImplementação de funcionalidades e correções de bugs, garantindo a qualidade e a eficiência do código.\nColaboração em equipe ágil, participando ativamente de sprints e reuniões de desenvolvimento para garantir entregas no prazo."],
                ],
                [
                    'company' => ['pt' => '2º Ofício de Registro de Imóveis de Belém'],
                    'role' => ['pt' => 'Auxiliar de Cartório de Nível 2'],
                    'start_date' => '2024-04-01', 'end_date' => '2025-03-01',
                    'description' => ['pt' => "Emissão de certidões, garantindo precisão e conformidade com as normas legais.\nAtendimento ao cliente, oferecendo suporte eficiente e resolutivo, com foco na satisfação do público.\nOrganização e controle de documentos, mantendo a conformidade e eficiência nos processos administrativos.\nAcompanhamento de processos e registros, colaborando para a agilidade no atendimento e na entrega de serviços."],
                ],
            ];
            foreach ($experiences as $index => $experience) {
                Experience::create([...$experience, 'location' => 'Belém, PA', 'order' => $index]);
            }
        });
    }
}
