<?php


$EM_CONF[$_EXTKEY] = [
	'title' => 'RTE Processing Tester',
	'description' => 'Tester for RTE content processing configuration.',
	'category' => 'module',
	'author' => 'René Fritz',
	'author_email' => 'r.fritz@colorcube.de',
    'author_company' => 'Colorcube',
    'version' => '1.0.0',
    'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-8.7.999'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'Colorcube\\RteProcTester\\' => 'Classes'
        ]
    ]
];