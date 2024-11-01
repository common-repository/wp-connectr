<?php return array(
    'root' => array(
        'name' => 'reenhanced/wp-connectr',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '70ef32e81815942aa740d7caf5dc153e677c029c',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'league/container' => array(
            'pretty_version' => '4.2.2',
            'version' => '4.2.2.0',
            'reference' => 'ff346319ca1ff0e78277dc2311a42107cc1aab88',
            'type' => 'library',
            'install_path' => __DIR__ . '/../league/container',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'orno/di' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '~2.0',
            ),
        ),
        'psr/container' => array(
            'pretty_version' => '2.0.2',
            'version' => '2.0.2.0',
            'reference' => 'c71ecc56dfe541dbd90c5360474fbc405f8d5963',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/container',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/container-implementation' => array(
            'dev_requirement' => false,
            'provided' => array(
                0 => '^1.0',
            ),
        ),
        'reenhanced/wp-connectr' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '70ef32e81815942aa740d7caf5dc153e677c029c',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
