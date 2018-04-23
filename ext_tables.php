<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


if (TYPO3_MODE === 'BE') {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'web',
        'rteproctester',
        '',
        '',
        [
            'routeTarget' => \Colorcube\RteProcTester\Controller\RteProcTesterController::class . '::mainAction',
            'access' => 'user,group',
            'name' => 'web_rteproctester',
            'labels' => [
                'tabs_images' => [
                    'tab' => 'EXT:rte_proc_tester/ext_icon.png',
                ],
                'll_ref' => 'LLL:EXT:rte_proc_tester/Resources/Private/Language/locallang_module.xlf',
            ],
        ]
    );
}

