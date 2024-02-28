<?php

namespace Database\Seeders;

use App\Models\FeeStructure;
use Illuminate\Database\Seeder;

class FeeStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FeeStructure::create([
            'id' => 1,
            'campus_id' => '1',
            'student_class_id' => '1',
            'fee_type_id' => '4',
            'amount' => '500',
        ]);
        FeeStructure::create([
            'id' => 2,
            'campus_id' => '1',
            'student_class_id' => '2',
            'fee_type_id' => '4',
            'amount' => '1000',
        ]);
        FeeStructure::create([
            'id' => 3,
            'campus_id' => '1',
            'student_class_id' => '3',
            'fee_type_id' => '4',
            'amount' => '1500',
        ]);
        FeeStructure::create([
            'id' => 4,
            'campus_id' => '1',
            'student_class_id' => '4',
            'fee_type_id' => '4',
            'amount' => '2000',
        ]);
        FeeStructure::create([
            'id' => 5,
            'campus_id' => '1',
            'student_class_id' => '5',
            'fee_type_id' => '4',
            'amount' => '2500',
        ]);
        FeeStructure::create([
            'id' => 6,
            'campus_id' => '1',
            'student_class_id' => '6',
            'fee_type_id' => '4',
            'amount' => '3000',
        ]);
        FeeStructure::create([
            'id' => 7,
            'campus_id' => '1',
            'student_class_id' => '7',
            'fee_type_id' => '4',
            'amount' => '3500',
        ]);
        FeeStructure::create([
            'id' => 8,
            'campus_id' => '1',
            'student_class_id' => '8',
            'fee_type_id' => '4',
            'amount' => '4000',
        ]);
        FeeStructure::create([
            'id' => 9,
            'campus_id' => '1',
            'student_class_id' => '9',
            'fee_type_id' => '4',
            'amount' => '4500',
        ]);
        //campus 2
        FeeStructure::create([
            'id' => 10,
            'campus_id' => '2',
            'student_class_id' => '1',
            'fee_type_id' => '4',
            'amount' => '500',
        ]);
        FeeStructure::create([
            'id' => 11,
            'campus_id' => '2',
            'student_class_id' => '2',
            'fee_type_id' => '4',
            'amount' => '1000',
        ]);
        FeeStructure::create([
            'id' => 12,
            'campus_id' => '2',
            'student_class_id' => '3',
            'fee_type_id' => '4',
            'amount' => '1500',
        ]);
        FeeStructure::create([
            'id' => 13,
            'campus_id' => '2',
            'student_class_id' => '4',
            'fee_type_id' => '4',
            'amount' => '2000',
        ]);
        FeeStructure::create([
            'id' => 14,
            'campus_id' => '2',
            'student_class_id' => '5',
            'fee_type_id' => '4',
            'amount' => '2500',
        ]);
        FeeStructure::create([
            'id' => 15,
            'campus_id' => '2',
            'student_class_id' => '6',
            'fee_type_id' => '4',
            'amount' => '3000',
        ]);
        FeeStructure::create([
            'id' => 16,
            'campus_id' => '2',
            'student_class_id' => '7',
            'fee_type_id' => '4',
            'amount' => '3500',
        ]);
        FeeStructure::create([
            'id' => 17,
            'campus_id' => '2',
            'student_class_id' => '8',
            'fee_type_id' => '4',
            'amount' => '4000',
        ]);
        FeeStructure::create([
            'id' => 18,
            'campus_id' => '2',
            'student_class_id' => '9',
            'fee_type_id' => '4',
            'amount' => '4500',
        ]);
        //campus 3
        FeeStructure::create([
            'id' => 19,
            'campus_id' => '3',
            'student_class_id' => '1',
            'fee_type_id' => '4',
            'amount' => '500',
        ]);
        FeeStructure::create([
            'id' => 20,
            'campus_id' => '3',
            'student_class_id' => '2',
            'fee_type_id' => '4',
            'amount' => '1000',
        ]);
        FeeStructure::create([
            'id' => 21,
            'campus_id' => '3',
            'student_class_id' => '3',
            'fee_type_id' => '4',
            'amount' => '1500',
        ]);
        FeeStructure::create([
            'id' => 22,
            'campus_id' => '3',
            'student_class_id' => '4',
            'fee_type_id' => '4',
            'amount' => '2000',
        ]);
        FeeStructure::create([
            'id' => 23,
            'campus_id' => '3',
            'student_class_id' => '5',
            'fee_type_id' => '4',
            'amount' => '2500',
        ]);
        FeeStructure::create([
            'id' => 24,
            'campus_id' => '3',
            'student_class_id' => '6',
            'fee_type_id' => '4',
            'amount' => '3000',
        ]);
        FeeStructure::create([
            'id' => 25,
            'campus_id' => '3',
            'student_class_id' => '7',
            'fee_type_id' => '4',
            'amount' => '3500',
        ]);
        FeeStructure::create([
            'id' => 26,
            'campus_id' => '3',
            'student_class_id' => '8',
            'fee_type_id' => '4',
            'amount' => '4000',
        ]);
        FeeStructure::create([
            'id' => 27,
            'campus_id' => '3',
            'student_class_id' => '9',
            'fee_type_id' => '4',
            'amount' => '4500',
        ]);
    }
}
