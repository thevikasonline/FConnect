<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
return [
    'application' => [
        'db' => [
            "dbhost" => '127.0.0.1',
            "dbuser" => 'root',
            "dbpass" => 'indianic',
            "dbname" => 'vikas_social',
        ],
        'module_dir' => '/app',
        'modules' => [
            [
                'name' => 'facebook',
                'default' => true,
                'credentials' => [
                    'api_key' => '',
                    'api_secret' => '',
                    'call_back' => '',
                ]
            ],
        ]
    ]
];
?>
