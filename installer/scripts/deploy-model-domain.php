<?php

use Sytesbook\WPWedding\Deploy\Executors\Cleanup;
use Sytesbook\WPWedding\Deploy\Executors\SymlinkSetup;
use Sytesbook\WPWedding\Deploy\FileSystem\Filters\Folder;
use Sytesbook\WPWedding\Deploy\FileSystem\Filters\SymbolicLink;

return [
    'title' => 'Deploy model domain',
    'steps' => [
        [
            'title' => 'Pre-deploy cleanup',
            'executor' => Cleanup::class,
            'params'=> [
                'delete' => [
                    '{domain_folder}' => [
                        new SymbolicLink()
                    ]
                ]
            ]
        ],
        [
            'title' => 'Setup symbolic links',
            'executor' => SymlinkSetup::class,
            'params'=> [
                'symlinks' => [
                    // Domain folder
                    '{domain_folder}/uploads' => '{deployment_folder}/uploads',
                    '{domain_folder}/wp-content' => '{deployment_folder}/src/wp-content',
                    '{domain_folder}/wp-includes' => '{deployment_folder}/wp/wp-includes',
                    '{domain_folder}/index.php' => '{deployment_folder}/wp/index.php',
                    '{domain_folder}/.htaccess' => '{deployment_folder}/wp/.htaccess',
                ]
            ]
        ],
        [
            'title' => 'Post-deploy cleanup',
            'executor' => Cleanup::class,
            'params'=> [
                'delete' => [
                    '{domain_folder}' => [
                        new Folder('installer')
                    ]
                ]
            ]
        ],
    ]
];
