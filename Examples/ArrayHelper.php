<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require '../src/helpers/Dev.php';
require '../src/helpers/ArrayHelper.php';

use SouthCoast\Helpers\Dev;
use SouthCoast\Helpers\ArrayHelper;


$array = [
    [
        'firstname' => 'Tom',
        'lastname' => 'Jones',
        'age' => 43,
        'email' => [
            'tom@jones.com',
            'tommy232@hotmail.com'
        ],
        'groups' => [
            'party',
            'property'
        ],
        'friends' => [
            [
                'firstname' => 'Sia',
                'lastname' => 'Takens',
                'age' => 32,
                'email' => [
                    's.takens@gmail.com',
                    'sisasia@hotmail.com'
                ],
                'groups' => [
                    'breakfast',
                    'property'
                ],
            ],
            [
                'firstname' => 'Anna',
                'lastname' => 'Pickens',
                'age' => 43,
                'email' => [
                    'annapicks@me.com',
                ],
                'groups' => [
                    'party',
                    'property',
                    'breakfast',
                    'dancefloor'
                ],
            ]
        ]
    ],
    [
        'firstname' => 'Sia',
        'lastname' => 'Takens',
        'age' => 32,
        'email' => [
            's.takens@gmail.com',
            'sisasia@hotmail.com'
        ],
        'groups' => [
            'breakfast',
            'property'
        ],
    ],
    [
        'firstname' => 'Anna',
        'lastname' => 'Pickens',
        'age' => 43,
        'email' => [
            'annapicks@me.com',
        ],
        'groups' => [
            'party',
            'property',
            'breakfast',
            'dancefloor'
        ],
    ]
];


// print_r(ArrayHelper::getMultiple($array, '?.firstname', '?.lastname', '?.age'));

// var_dump(ArrayHelper::get('0.friends', $array));
var_dump(ArrayHelper::search('Anna', $array));