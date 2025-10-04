<?php

namespace App\Controller;

use BookManagement\Application\BookService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class BookController {
    private BookService $bookService;

    public function __construct(BookService $bookService) {
        $this->bookService = $bookService;
    }

    public function index(Request $request): Response {
        $limit = $request->query->getInt('limit', 100);
        $offset = $request->query->getInt('offset', 0);
        $title = $request->query->get('title');
        $author = $request->query->get('author');

        if ($title) {
            $books = $this->bookService->searchBooksByTitle($title);
        } elseif ($author) {
            $books = $this->bookService->searchBooksByAuthor($author);
        } else {
            $books = $this->bookService->findAllBooks($limit, $offset);
        }
        
        $booksArray = array_map(fn($book) => $book->toArray(), $books);
        return new JsonResponse($booksArray, Response::HTTP_OK);
    }

    public function show(Request $request, int $id): Response {
        $book = $this->bookService->findBookById($id);

        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($book->toArray(), Response::HTTP_OK);
    }

    public function create(Request $request): Response {
        // Check if the request is JSON
        $contentType = $request->headers->get('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }
        
        // Validate required fields
        $requiredFields = ['title', 'author', 'isbn', 'publication_year'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return new JsonResponse(
                    ['error' => "Missing or empty required field: $field"], 
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        
        try {
            $book = $this->bookService->createBook($data);
            
            $response = new JsonResponse($book->toArray(), Response::HTTP_CREATED);
            $response->headers->set('Location', "/books/{$book->getId()}");
            return $response;
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()], 
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function update(Request $request, int $id): Response {
        $data = $request->toArray();

        try {
            $book = $this->bookService->updateBook($id, $data);
            
            return new JsonResponse($book->toArray(), Response::HTTP_OK);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request, int $id): Response {
        $result = $this->bookService->deleteBook($id);
        
        return new Response('', $result ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND);
    }
}

