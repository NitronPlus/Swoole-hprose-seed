<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Simple as View;
use Phalcon\Queue\Beanstalk as Queue;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Output as FrontOutput;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * Sets the view component
 */
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setViewsDir($config->application->viewsDir);
    return $view;
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);
    return $url;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);

    return $connection;
});
/**
 * MonoLog
 */
$di->setShared('log', function(){
    $logger = new Logger('app');
    $logger->pushHandler(new StreamHandler(
        storage_path('logs') .'/'. date("Y-m-d-H") . '.log', Logger::DEBUG
    ));
    return $logger;
});
/**
 * Beanstalk Queue
 */
$di->setShared('queue', function(){
    $queue = new Queue([
        "host" => "127.0.0.1",
        "port" => "11300",
    ]);

    return $queue;
});
/**
 * Cache
 */
$di->setShared('cache', function(){
    $frontCache = new FrontOutput([
        "lifetime" => 3600,
    ]);

    $cache = new BackFile(
        $frontCache,
        ["cacheDir" => STORAGE_PATH . "/cache/",]
    );

    return $cache;
});

