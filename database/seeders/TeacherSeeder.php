<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * Seed 10 teachers spread across the departments.
     */
    public function run(): void
    {
        $teachers = [
            ['T001', 'Sok Dara', 'male', 'Mathematics', '2015-08-15'],
            ['T002', 'Chan Sophea', 'female', 'Science', '2016-09-01'],
            ['T003', 'Keo Rathana', 'female', 'English', '2018-01-10'],
            ['T004', 'Lim Visal', 'male', 'Social Studies', '2014-07-20'],
            ['T005', 'Heng Bopha', 'female', 'Information Technology', '2020-02-03'],
            ['T006', 'Meas Sovann', 'male', 'Science', '2019-08-26'],
            ['T007', 'Phan Sreymom', 'female', 'Mathematics', '2021-09-01'],
            ['T008', 'Ouk Pisey', 'female', 'English', '2017-03-14'],
            ['T009', 'Ros Vichea', 'male', 'Social Studies', '2022-08-29'],
            ['T010', 'Nhem Kosal', 'male', 'Science', '2023-01-09'],
        ];

        $departmentIds = Department::pluck('id', 'name');

        foreach ($teachers as [$code, $name, $gender, $department, $hireDate]) {
            Teacher::firstOrCreate(['teacher_code' => $code], Teacher::factory()->raw([
                'teacher_code' => $code,
                'name' => $name,
                'gender' => $gender,
                'email' => strtolower(str_replace(' ', '.', $name)).'@school.test',
                'hire_date' => $hireDate,
                'department_id' => $departmentIds[$department],
            ]));
        }
    }
}
