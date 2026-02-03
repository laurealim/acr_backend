<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Office;
use App\Models\Employee;
use App\Models\OfficeAdmin;
use Illuminate\Support\Facades\Hash;

class ACRDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Office Hierarchy
        $ministry = Office::create([
            'name_bangla' => 'জনপ্রশাসন মন্ত্রণালয়',
            'name_english' => 'Ministry of Public Administration',
            'code' => 'MOPA',
            'type' => 'ministry',
            'address' => 'বাংলাদেশ সচিবালয়, ঢাকা',
            'phone' => '02-9540001',
            'email' => 'info@mopa.gov.bd',
            'is_active' => true,
        ]);

        $division = Office::create([
            'name_bangla' => 'প্রশাসন বিভাগ',
            'name_english' => 'Administration Division',
            'code' => 'MOPA-AD',
            'type' => 'division',
            'parent_id' => $ministry->id,
            'is_active' => true,
        ]);

        $department = Office::create([
            'name_bangla' => 'জনশক্তি পরিকল্পনা অধিদপ্তর',
            'name_english' => 'Directorate of Manpower Planning',
            'code' => 'MOPA-DMP',
            'type' => 'department',
            'parent_id' => $division->id,
            'is_active' => true,
        ]);

        $office = Office::create([
            'name_bangla' => 'ঢাকা আঞ্চলিক কার্যালয়',
            'name_english' => 'Dhaka Regional Office',
            'code' => 'MOPA-DMP-DRO',
            'type' => 'office',
            'parent_id' => $department->id,
            'address' => 'আগারগাঁও, ঢাকা',
            'is_active' => true,
        ]);

        // Create Users and Employees

        // 1. Admin User (already exists from UserSeeder, just create employee)
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            Employee::create([
                'user_id' => $adminUser->id,
                'office_id' => $ministry->id,
                'employee_id' => 'ADM-001',
                'name_bangla' => 'প্রশাসক',
                'name_english' => 'System Administrator',
                'date_of_birth' => '1980-01-01',
                'father_name' => 'পিতার নাম',
                'mother_name' => 'মাতার নাম',
                'gender' => 'male',
                'marital_status' => 'married',
                'grade' => 3,
                'employee_class' => '1st_class',
                'designation' => 'সচিব',
                'govt_service_join_date' => '2000-01-01',
                'current_position_join_date' => '2020-01-01',
                'highest_education' => 'মাস্টার্স',
                'is_dossier_keeper' => true,
                'is_active' => true,
            ]);
        }

        // 2. First Class Officer (IO)
        $ioUser = User::create([
            'name' => 'Initiating Officer',
            'email' => 'io@example.com',
            'password' => Hash::make('password123'),
        ]);

        $ioEmployee = Employee::create([
            'user_id' => $ioUser->id,
            'office_id' => $office->id,
            'employee_id' => 'EMP-IO-001',
            'name_bangla' => 'আহমেদ হাসান',
            'name_english' => 'Ahmed Hasan',
            'date_of_birth' => '1975-05-15',
            'father_name' => 'করিম হাসান',
            'mother_name' => 'ফাতেমা বেগম',
            'gender' => 'male',
            'marital_status' => 'married',
            'number_of_children' => 2,
            'grade' => 5,
            'employee_class' => '1st_class',
            'designation' => 'উপসচিব',
            'cadre' => 'প্রশাসন',
            'batch' => '২০তম',
            'govt_service_join_date' => '2000-06-01',
            'current_position_join_date' => '2018-01-01',
            'highest_education' => 'মাস্টার্স ইন পাবলিক অ্যাডমিনিস্ট্রেশন',
            'is_active' => true,
        ]);

        // 3. First Class Officer (CO)
        $coUser = User::create([
            'name' => 'Countersigning Officer',
            'email' => 'co@example.com',
            'password' => Hash::make('password123'),
        ]);

        $coEmployee = Employee::create([
            'user_id' => $coUser->id,
            'office_id' => $department->id,
            'employee_id' => 'EMP-CO-001',
            'name_bangla' => 'ড. রহিম উদ্দিন',
            'name_english' => 'Dr. Rahim Uddin',
            'date_of_birth' => '1970-03-20',
            'father_name' => 'আব্দুল করিম',
            'mother_name' => 'জাহানারা বেগম',
            'gender' => 'male',
            'marital_status' => 'married',
            'number_of_children' => 3,
            'grade' => 3,
            'employee_class' => '1st_class',
            'designation' => 'যুগ্মসচিব',
            'cadre' => 'প্রশাসন',
            'batch' => '১৫তম',
            'govt_service_join_date' => '1995-01-01',
            'current_position_join_date' => '2015-01-01',
            'highest_education' => 'পিএইচডি',
            'is_active' => true,
        ]);

        // 4. Staff Employee (normal user)
        $staffUser = User::where('email', 'user@example.com')->first();
        if (!$staffUser) {
            $staffUser = User::create([
                'name' => 'Staff User',
                'email' => 'user@example.com',
                'password' => Hash::make('password123'),
            ]);
        }

        $staffEmployee = Employee::create([
            'user_id' => $staffUser->id,
            'office_id' => $office->id,
            'employee_id' => 'EMP-STAFF-001',
            'name_bangla' => 'মোহাম্মদ আলী',
            'name_english' => 'Mohammad Ali',
            'date_of_birth' => '1985-08-10',
            'father_name' => 'আব্দুল আলী',
            'mother_name' => 'সাকিনা বেগম',
            'gender' => 'male',
            'marital_status' => 'married',
            'number_of_children' => 1,
            'grade' => 12,
            'employee_class' => '2nd_class',
            'designation' => 'সহকারী',
            'govt_service_join_date' => '2010-07-01',
            'current_position_join_date' => '2015-01-01',
            'highest_education' => 'স্নাতক',
            'is_active' => true,
        ]);

        // 5. Another Staff Employee
        $staff2User = User::create([
            'name' => 'Staff Two',
            'email' => 'staff2@example.com',
            'password' => Hash::make('password123'),
        ]);

        Employee::create([
            'user_id' => $staff2User->id,
            'office_id' => $office->id,
            'employee_id' => 'EMP-STAFF-002',
            'name_bangla' => 'ফাতিমা আক্তার',
            'name_english' => 'Fatima Akter',
            'date_of_birth' => '1990-02-25',
            'father_name' => 'আবু বকর',
            'mother_name' => 'আয়েশা খাতুন',
            'gender' => 'female',
            'marital_status' => 'married',
            'number_of_children' => 2,
            'grade' => 13,
            'employee_class' => '2nd_class',
            'designation' => 'অফিস সহায়ক',
            'govt_service_join_date' => '2012-01-01',
            'current_position_join_date' => '2018-06-01',
            'highest_education' => 'এইচএসসি',
            'is_active' => true,
        ]);

        // 6. Dossier Keeper
        $dossierUser = User::create([
            'name' => 'Dossier Keeper',
            'email' => 'dossier@example.com',
            'password' => Hash::make('password123'),
        ]);

        Employee::create([
            'user_id' => $dossierUser->id,
            'office_id' => $office->id,
            'employee_id' => 'EMP-DSK-001',
            'name_bangla' => 'করিম শেখ',
            'name_english' => 'Karim Sheikh',
            'date_of_birth' => '1982-11-15',
            'father_name' => 'শেখ আহমেদ',
            'mother_name' => 'মরিয়ম বেগম',
            'gender' => 'male',
            'marital_status' => 'married',
            'number_of_children' => 2,
            'grade' => 9,
            'employee_class' => '1st_class',
            'designation' => 'প্রশাসনিক কর্মকর্তা',
            'govt_service_join_date' => '2005-01-01',
            'current_position_join_date' => '2019-01-01',
            'highest_education' => 'মাস্টার্স',
            'is_dossier_keeper' => true,
            'is_active' => true,
        ]);

        // Create Office Admin
        if ($adminUser) {
            OfficeAdmin::create([
                'user_id' => $adminUser->id,
                'office_id' => $office->id,
                'can_assign_dossier' => true,
                'can_manage_employees' => true,
                'is_active' => true,
            ]);
        }

        $this->command->info('ACR Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('Demo Credentials:');
        $this->command->info('-------------------');
        $this->command->info('Admin: admin@example.com / password123');
        $this->command->info('IO (1st Class): io@example.com / password123');
        $this->command->info('CO (1st Class): co@example.com / password123');
        $this->command->info('Staff: user@example.com / password123');
        $this->command->info('Staff 2: staff2@example.com / password123');
        $this->command->info('Dossier Keeper: dossier@example.com / password123');
    }
}
