<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['Ciências e Tecnologias', 'Ciências Sócio-Económicas', 'Gestão Desportiva', 
        'Interprete/Ator/Atriz', 'Línguas e Humanidades',
        'Turismo', 'Operações Turísticas', 'Gestão Prog. Sist. Informáticos',
        'Auxiliar de Saúde', 'Sistemas', 'Artes Visuais', 'Indústrias Alimentares e Análise Laboratorial'] as $courseName) {
            Course::firstOrCreate(['name' => $courseName], ['is_active' => true]);
        }

        foreach ([
            ['name' => '1.11', 'grade_level' => 10],
            ['name' => '1.12TT', 'grade_level' => 10],
            ['name' => '1.12OT', 'grade_level' => 10],
            ['name' => '1.13AS', 'grade_level' => 10],
            ['name' => '1.13IAA', 'grade_level' => 10],
            ['name' => '1.14', 'grade_level' => 10],
            ['name' => '1.15', 'grade_level' => 12],
            ['name' => '2.11', 'grade_level' => 11],
            ['name' => '2.12TT', 'grade_level' => 11],
            ['name' => '2.12OT', 'grade_level' => 11],
            ['name' => '2.13AS', 'grade_level' => 11],
            ['name' => '2.13IAA', 'grade_level' => 11],
            ['name' => '2.14', 'grade_level' => 11],
            ['name' => '2.15', 'grade_level' => 11],
            ['name' => '3.11', 'grade_level' => 12],
            ['name' => '3.12TT', 'grade_level' => 12],
            ['name' => '3.12OT', 'grade_level' => 12],
            ['name' => '3.13IAA', 'grade_level' => 12],
            ['name' => '3.13PSI', 'grade_level' => 12],
            ['name' => '3.14', 'grade_level' => 12],
            ['name' => '10.1', 'grade_level' => 10],
            ['name' => '10.2', 'grade_level' => 10],
            ['name' => '10.3', 'grade_level' => 10],
            ['name' => '10.4', 'grade_level' => 10],
            ['name' => '10.5', 'grade_level' => 10],
            ['name' => '10.6', 'grade_level' => 10],
            ['name' => '10.7', 'grade_level' => 10],
            ['name' => '11.1', 'grade_level' => 11],
            ['name' => '11.2', 'grade_level' => 11],
            ['name' => '11.3', 'grade_level' => 11],
            ['name' => '11.4', 'grade_level' => 11],
            ['name' => '11.5', 'grade_level' => 11],
            ['name' => '11.6', 'grade_level' => 11],
            ['name' => '11.7', 'grade_level' => 11],
            ['name' => '11.8', 'grade_level' => 11],
            ['name' => '12.1', 'grade_level' => 12],
            ['name' => '12.2', 'grade_level' => 12],
            ['name' => '12.3', 'grade_level' => 12],
            ['name' => '12.4', 'grade_level' => 12],
            ['name' => '12.5', 'grade_level' => 12],
            ['name' => '12.6', 'grade_level' => 12],
            ['name' => '12.7', 'grade_level' => 12],

        ] as $class) {
            SchoolClass::firstOrCreate(['name' => $class['name']], [
                'grade_level' => $class['grade_level'],
                'is_active' => true,
            ]);
        }

        /*
         * Course subjects.
         *
         * To add or remove a subject later:
         * 1. Find the course name below.
         * 2. Add or remove a subject name in that array.
         * 3. Run: php artisan db:seed
         */
        $courseSubjects = [
            'Ciências e Tecnologias' => [
                'Português',
                'Filosofia',
                'Educação Física',
                'Matemática A',
                'Física e Química A',
                'Biologia e Geologia',
                'Geometria Descritiva A',
                'Física',
                'Química',
                'Biologia',
                'Geologia',
                'Aplicações Informáticas B',
            ],
            'Ciências Sócio-Económicas' => [
                'Português',
                'Filosofia',
                'Educação Física',
                'Matemática A',
                'Economia A',
                'Geografia A',
                'Economia C',
                'Sociologia',
                'Psicologia B',
            ],
            'Línguas e Humanidades' => [
                'Português',
                'Filosofia',
                'Educação Física',
                'História A',
                'Geografia A',
                'MACS',
                'Espanhol',
                'Geografia C',
                'Psicologia B',
                'Sociologia',
            ],
            'Artes Visuais' => [
                'Português',
                'Filosofia',
                'Educação Física',
                'Desenho A',
                'Geometria Descritiva A',
                'História da Cultura e das Artes',
                'Oficina de Artes',
                'Oficina Multimédia',
            ],
            'Gestão Prog. Sist. Informáticos' => [
                'Português',
                'Inglês',
                'Área de Integração',
                'TIC',
                'Educação Física',
                'Matemática',
                'Física',
                'Programação e Sistemas de Informação',
                'Redes de Comunicação',
                'Arquitetura de Computadores',
            ],
            'Sistemas' => [
                'Português',
                'Inglês',
                'Área de Integração',
                'TIC',
                'Educação Física',
                'Matemática',
                'Física',
                'Programação e Sistemas de Informação',
                'Redes de Comunicação',
                'Arquitetura de Computadores',
            ],
            'Turismo' => [
                'Português',
                'Inglês',
                'Área de Integração',
                'TIC',
                'Educação Física',
                'Comunicar em Espanhol',
                'Geografia do Turismo',
                'Técnicas de Comunicação e Acolhimento',
                'Operações Técnicas em Empresas Turísticas',
                'História do Turismo',
            ],
            'Operações Turísticas' => [
                'Português',
                'Inglês',
                'Área de Integração',
                'TIC',
                'Educação Física',
                'Comunicar em Espanhol',
                'Geografia do Turismo',
                'Técnicas de Comunicação e Acolhimento',
                'Operações Técnicas em Empresas Turísticas',
                'História do Turismo',
            ],
            'Auxiliar de Saúde' => [
                'Português',
                'Inglês',
                'Área de Integração',
                'TIC',
                'Educação Física',
                'Saúde',
                'Biologia',
                'Anatomia e Fisiologia',
                'Técnicas de Auxílio à Saúde',
            ],
            'Indústrias Alimentares e Análise Laboratorial' => [
                'Português',
                'Inglês',
                'Área de Integração',
                'TIC',
                'Educação Física',
                'Química',
                'Microbiologia Alimentar',
                'Processamento de Alimentos',
                'Controlo de Qualidade',
            ],
            'Gestão Desportiva' => [
                'Português',
                'Inglês',
                'Área de Integração',
                'TIC',
                'Educação Física',
                'Organização e Gestão do Desporto',
                'Estudo do Movimento',
                'Prática Desportiva',
            ],
            'Interprete/Ator/Atriz' => [
                'Português',
                'Inglês',
                'Área de Integração',
                'TIC',
                'Educação Física',
                'Interpretação',
                'Expressão Corporal',
                'Expressão Vocal',
                'Dramaturgia',
                'História da Cultura e das Artes',
            ],
        ];

        foreach ($courseSubjects as $courseName => $subjectNames) {
            $course = Course::firstOrCreate(['name' => $courseName], ['is_active' => true]);

            foreach ($subjectNames as $subjectName) {
                $subject = Subject::firstOrCreate(['name' => $subjectName], ['is_active' => true]);

                $course->subjects()->syncWithoutDetaching($subject->id);
            }
        }

    }
}
