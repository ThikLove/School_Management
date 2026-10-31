<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed the school's departments.
     */
    public function run(): void
    {
        $departments = [
            'Science' => 'Physics, chemistry and biology teaching staff.',
            'Mathematics' => 'Algebra, geometry, statistics and calculus.',
            'English' => 'English language, literature and communication skills.',
            'Social Studies' => 'History, geography and civic education.',
            'Information Technology' => 'Computer science, digital literacy and programming.',
        ];

        foreach ($departments as $name => $description) {
            Department::firstOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
