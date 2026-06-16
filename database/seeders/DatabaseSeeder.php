<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\SchoolClass;
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
        'Auxiliar de Saúde', 'Sistemas', ] as $courseName) {
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

    }
}
