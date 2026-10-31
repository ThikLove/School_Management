<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Local-development password shared by all demo accounts.
     */
    public const DEMO_PASSWORD = 'password';

    /**
     * Seed one login account per role and link the teacher/student accounts to their profiles.
     *
     * These accounts use a well-known password, so they are never created in production.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Skipping demo accounts in production.');

            return;
        }

        $this->demoUser('admin@school.test', 'School Admin', User::ROLE_ADMIN);

        $teacherUser = $this->demoUser('teacher@school.test', 'Sok Dara', User::ROLE_TEACHER);
        Teacher::where('teacher_code', 'T001')->update(['user_id' => $teacherUser->id]);

        $studentUser = $this->demoUser('student@school.test', 'Chea Vibol', User::ROLE_STUDENT);
        Student::where('student_code', 'S0001')->update(['user_id' => $studentUser->id]);
    }

    private function demoUser(string $email, string $name, string $role): User
    {
        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->name = $name;
            $user->password = self::DEMO_PASSWORD;
            $user->email_verified_at = now();
        }

        $user->role = $role;
        $user->save();

        return $user;
    }
}
