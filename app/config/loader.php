<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    [
        $config->application->controllersDir,
    ]
);
$loader->registerNamespaces(
    [
        'MyCrawler' => $config->application->libraryDir . '/MyCrawler',
    ]
);
$loader->registerFiles(
    [
        APP_PATH . '/vendor/autoload.php', // composer
    ]
);
$loader->register();
