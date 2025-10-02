<?php

use BookManagement\Infrastructure\Http\BookController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('/books', function (RouteCollectorProxy $group) {
        $group->get('', [BookController::class, 'index']);
        $group->get('/{id}', [BookController::class, 'show']);
        $group->post('', [BookController::class, 'create']);
        $group->put('/{id}', [BookController::class, 'update']);
        $group->delete('/{id}', [BookController::class, 'delete']);
    });
};

