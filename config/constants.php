<?php

return [
    'student_statuses' => [
        'registered_only' => 1,
        'admission_only' => 2,
        'applied_for_admission' => 3,
        'struck_off' => 4,
        'passout' => 5,

    ],

    'employee_status' => [
        'registered_only' => 1,
        'appointed_only' => 2,

    ],

    'fee_challan_statuses' => [
        'not_submitted' => 0,
        'submitted' => 1,
        'posted_to_ledger' => 2,

    ],

    'salary_statuses' => [
        'unpaid' => 0,
        'paid' => 1,
        'posted_to_ledger' => 2,

    ],
    'exam_statuses' => [
        'not_complete' => 0,
        'complete' => 1,
        'announced' => 2,

    ],
    // 'code_filter_count' => [
    //     1 => '1000',
    //     2 => '2000',
    //     3 => '3000',
    //     4 => '4000',
    //     5 => '5000',
    //     6 => '6000',
    //     7 => '7000',
    //     8 => '8000',
    //     9 => '9000',
    //     10 => '10000',
    //     11 => '11000',
    //     12 => '12000',
    //     13 => '13000',
    //     14 => '14000',
    //     15 => '15000',
    //     16 => '16000',
    //     17 => '17000',
    // ],

    // 'two_boolean_options' => [
    //     '1' => 'No',
    //     '2' => 'Yes',
    // ],

    'sms_regards' => [
        'regards' => "\n".'Thanks, Sender: Dar-e-Arqam Sargodha.'."\n".' To block promotions from DAR-E-ARQAM send UNSUB to 6661724. To get all promotions, send REG to 3627',
    ],
];
