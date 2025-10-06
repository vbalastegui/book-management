<?php

namespace App\Controller;

use BookManagement\Application\BookService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateBookController {
    private BookService $bookService;

    public function __construct(BookService $bookService) {
        $this->bookService = $bookService;
    }

    public function __invoke(Request $request): Response {
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
}
