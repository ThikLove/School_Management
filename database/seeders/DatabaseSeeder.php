<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Parent tables are seeded before the tables that reference them, and everything runs in
     * one transaction so a failure part-way through leaves no half-seeded data behind.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->call([
                DepartmentSeeder::class,
                SubjectSeeder::class,
                TeacherSeeder::class,
                SchoolClassSeeder::class,
                ClassSubjectSeeder::class,
                StudentSeeder::class,
                EnrollmentSeeder::class,
                AttendanceSeeder::class,
                DemoUserSeeder::class,
            ]);
        });
    }
}
