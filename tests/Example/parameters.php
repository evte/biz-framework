<?php

return array(
    'env'      => 'dev',
    'debug'    => true,
    'database' => array(
        'driver'   => 'pdo_mysql',
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'biz_framework_test',
        'user'     => 'root',
        'password' => '',
        'charset'  => 'utf8'
    ),
    'cache'    => array(
        'default' => array(
            "host"           => "127.0.0.1",
            "port"           => 6378,
            "timeout"        => 1,
            "reserved"       => null,
            "retry_interval" => 100
        )
    )
);
