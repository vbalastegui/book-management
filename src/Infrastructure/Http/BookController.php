<?php

namespace BookManagement\Infrastructure\Http;

use BookManagement\Application\BookService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BookController {
    private BookService $bookService;

    public function __construct(BookService $bookService) {
        $this->bookService = $bookService;
    }

    public function index(Request $request, Response $response): Response {
        $params = $request->getQueryParams();
        $limit = isset($params['limit']) ? (int)$params['limit'] : 100;
        $offset = isset($params['offset']) ? (int)$params['offset'] : 0;

        if (isset($params['title'])) {
            $books = $this->bookService->searchBooksByTitle($params['title']);
        } elseif (isset($params['author'])) {
            $books = $this->bookService->searchBooksByAuthor($params['author']);
        } else {
            $books = $this->bookService->findAllBooks($limit, $offset);
        }
        
        $response->getBody()->write(json_encode($books, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function show(Request $request, Response $response, array $args): Response {
        $id = (int)$args['id'];
        $book = $this->bookService->findBookById($id);

        if (!$book) {
            $response->getBody()->write(json_encode(['error' => 'Book not found'], JSON_PRETTY_PRINT));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode($book, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response {
        $data = json_decode($request->getBody()->getContents(), true);
        
        try {
            $book = $this->bookService->createBook($data);
            
            $response->getBody()->write(json_encode($book, JSON_PRETTY_PRINT));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }

    public function update(Request $request, Response $response, array $args): Response {
        $id = (int)$args['id'];
        $data = json_decode($request->getBody()->getContents(), true);

        try {
            $book = $this->bookService->updateBook($id, $data);
            
            $response->getBody()->write(json_encode($book, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response {
        $id = (int)$args['id'];

        $result = $this->bookService->deleteBook($id);
        
        return $response
            ->withStatus($result ? 204 : 404);
    }
}

