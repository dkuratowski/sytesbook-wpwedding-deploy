<?php

use Sytesbook\WPWedding\Deploy\Executors\Cleanup;
use Sytesbook\WPWedding\Deploy\Executors\Extract;
use Sytesbook\WPWedding\Deploy\Executors\SymlinkSetup;
use Sytesbook\WPWedding\Deploy\FileSystem\Filters\Folder;
use Sytesbook\WPWedding\Deploy\FileSystem\Filters\EnvFile;
use Sytesbook\WPWedding\Deploy\FileSystem\Filters\File;
use Sytesbook\WPWedding\Deploy\FileSystem\Filters\PackageZipFile;
use Sytesbook\WPWedding\Deploy\FileSystem\Filters\SymbolicLink;

return [
    'title' => 'Deploy system',
    'steps' => [
        [
            'title' => 'Pre-deploy cleanup',
            'executor' => Cleanup::class,
            'params'=> [
                'delete' => [
                    '{deployment_folder}' => [
                        new Folder('src'),
                        new Folder('wp'),
                        new Folder('vendor'),
                        new EnvFile(),
                        new File('version.json'),
                        new File('.ftp-deploy-sync-state.json'),
                        new PackageZipFile(['{package_id}'])    // except the current package
                    ],
                    '{domain_folder}' => [
                        new SymbolicLink()
                    ]
                ]
            ]
        ],
        [
            'title' => 'Extract package',
            'executor' => Extract::class,
            'params'=> [
                'extract' => [
                    '{deployment_folder}/package_{package_id}.zip' => '{deployment_folder}'
                ]
            ]
        ],
        // [
        //     'title' => 'Setup symbolic links',
        //     'executor' => SymlinkSetup::class,
        //     'params'=> [
        //         'symlinks' => [
        //             // Deployment folder
        //             '{deployment_folder}/wp/migrations' => '{deployment_folder}/src/migrations',
        //             '{deployment_folder}/wp/uploads' => '{deployment_folder}/uploads',
        //             '{deployment_folder}/wp/wp-content' => '{deployment_folder}/src/wp-content',
        //             '{deployment_folder}/wp/wp-config.php' => '{deployment_folder}/src/wp-config.php',

        //             // Domain folder
        //             '{domain_folder}/uploads' => '{deployment_folder}/uploads',
        //             '{domain_folder}/wp-admin' => '{deployment_folder}/wp/wp-admin',
        //             '{domain_folder}/wp-content' => '{deployment_folder}/src/wp-content',
        //             '{domain_folder}/wp-includes' => '{deployment_folder}/wp/wp-includes',
        //             '{domain_folder}/wp-login.php' => '{deployment_folder}/wp/wp-login.php',
        //             '{domain_folder}/index.php' => '{deployment_folder}/wp/index.php',
        //             '{domain_folder}/.htaccess' => '{deployment_folder}/wp/.htaccess',
        //         ]
        //     ]
        // ],
        // [
        //     'title' => 'Post-deploy cleanup',
        //     'executor' => Cleanup::class,
        //     'params'=> [
        //         'delete' => [
        //             '{deployment_folder}' => [
        //                 new File('.ftp-deploy-sync-state.json'),
        //                 new PackageZipFile()
        //             ],
        //             '{domain_folder}' => [
        //                 new Folder('installer')
        //             ]
        //         ]
        //     ]
        // ],
    ]
];
