<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BookManagement\Application\BookService;
use BookManagement\Infrastructure\SqliteBookRepository;
use BookManagement\Infrastructure\OpenLibraryApiService;

header('Content-Type: application/json');

$repository = new SqliteBookRepository();
$apiService = new OpenLibraryApiService();
$bookService = new BookService($repository, $apiService);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                $book = $bookService->findBookById($id);
                echo json_encode($book ? $book : ['error' => 'Book not found'], JSON_PRETTY_PRINT);
            } elseif (isset($_GET['title'])) {
                $books = $bookService->searchBooksByTitle($_GET['title']);
                echo json_encode($books, JSON_PRETTY_PRINT);
            } elseif (isset($_GET['author'])) {
                $books = $bookService->searchBooksByAuthor($_GET['author']);
                echo json_encode($books, JSON_PRETTY_PRINT);
            } else {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                $books = $bookService->findAllBooks($limit, $offset);
                echo json_encode($books, JSON_PRETTY_PRINT);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $book = $bookService->createBook($data);
            http_response_code(201);
            echo json_encode($book, JSON_PRETTY_PRINT);
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Book ID is required'], JSON_PRETTY_PRINT);
                break;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $book = $bookService->updateBook($id, $data);
            echo json_encode($book, JSON_PRETTY_PRINT);
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Book ID is required'], JSON_PRETTY_PRINT);
                break;
            }
            $result = $bookService->deleteBook($id);
            http_response_code($result ? 204 : 404);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed'], JSON_PRETTY_PRINT);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
