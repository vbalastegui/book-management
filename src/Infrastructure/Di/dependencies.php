<?php

use BookManagement\Application\BookService;
use BookManagement\Application\BookApiServiceInterface;
use BookManagement\Domain\BookRepositoryInterface;
use BookManagement\Infrastructure\SqliteBookRepository;
use BookManagement\Infrastructure\OpenLibraryApiService;
use BookManagement\Infrastructure\Http\BookController;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

return [
    BookRepositoryInterface::class => function (ContainerInterface $c) {
        $config = require __DIR__ . '/../../../config/database.php';
        return new SqliteBookRepository($config['path']);
    },

    BookApiServiceInterface::class => function (ContainerInterface $c) {
        return new OpenLibraryApiService();
    },

    Logger::class => function (ContainerInterface $c) {
        $logger = new Logger('BookManagement');
        $logger->pushHandler(
            new StreamHandler('/var/www/html/logs/bookservice.log', Logger::INFO)
        );
        return $logger;
    },

    BookService::class => function (ContainerInterface $c) {
        return new BookService(
            $c->get(BookRepositoryInterface::class),
            $c->get(BookApiServiceInterface::class),
            $c->get(Logger::class)
        );
    },

    BookController::class => function (ContainerInterface $c) {
        return new BookController(
            $c->get(BookService::class)
        );
    },
];

