<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Subject code => [name, description, department that teaches it].
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    public const SUBJECTS = [
        'MATH101' => ['Mathematics', 'Algebra, geometry and problem solving.', 'Mathematics'],
        'PHY101' => ['Physics', 'Motion, energy, electricity and waves.', 'Science'],
        'CHE101' => ['Chemistry', 'Atoms, reactions and laboratory practice.', 'Science'],
        'BIO101' => ['Biology', 'Cells, the human body and ecosystems.', 'Science'],
        'ENG101' => ['English', 'Reading, writing, grammar and speaking.', 'English'],
        'HIS101' => ['History', 'Cambodian and world history.', 'Social Studies'],
        'GEO101' => ['Geography', 'Maps, climate, population and resources.', 'Social Studies'],
        'ICT101' => ['Computer Science', 'Digital literacy, spreadsheets and basic programming.', 'Information Technology'],
    ];

    /**
     * Seed the subjects offered by the school.
     */
    public function run(): void
    {
        foreach (self::SUBJECTS as $code => [$name, $description]) {
            Subject::firstOrCreate(['subject_code' => $code], [
                'name' => $name,
                'description' => $description,
            ]);
        }
    }
}
