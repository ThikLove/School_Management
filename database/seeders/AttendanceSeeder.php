<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * How many recent school days (Monday to Friday) to record.
     */
    private const SCHOOL_DAYS = 10;

    /**
     * Seed attendance for every active student over the last school days.
     *
     * The status is derived from a hash of the student and date, so re-running the
     * seeder produces the same realistic mix (about 80% present) instead of random noise.
     */
    public function run(): void
    {
        $students = Student::where('status', 'active')->get();

        foreach ($this->recentSchoolDays() as $date) {
            foreach ($students as $student) {
                [$status, $remarks] = $this->statusFor($student->id, $date);

                Attendance::firstOrCreate([
                    'student_id' => $student->id,
                    'class_id' => $student->class_id,
                    'attendance_date' => $date,
                ], [
                    'status' => $status,
                    'remarks' => $remarks,
                ]);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function recentSchoolDays(): array
    {
        $days = [];
        $date = Carbon::today();

        while (count($days) < self::SCHOOL_DAYS) {
            if ($date->isWeekday()) {
                $days[] = $date->toDateString();
            }

            $date = $date->subDay();
        }

        return array_reverse($days);
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function statusFor(int $studentId, string $date): array
    {
        $roll = crc32("{$studentId}|{$date}") % 100;

        return match (true) {
            $roll < 80 => ['present', null],
            $roll < 88 => ['late', 'Arrived after the first period.'],
            $roll < 95 => ['absent', 'No notice received from parents.'],
            default => ['excused', 'Medical appointment (note provided).'],
        };
    }
}
