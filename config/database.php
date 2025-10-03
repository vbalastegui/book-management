<?php

return [
    'driver' => 'sqlite',
    'path' => getenv('DB_PATH') ?: '/var/www/html/data/books.sqlite',
];

