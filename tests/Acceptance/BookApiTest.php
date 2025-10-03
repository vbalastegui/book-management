<?php

namespace BookManagement\Tests\Acceptance;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;

/**
 * Acceptance Test: Book API (HTTP)
 * 
 * These tests validate the API from the user's perspective via HTTP requests
 */
class BookApiTest extends TestCase {
    private Client $client;
    private string $baseUrl = 'http://localhost:8080';

    protected function setUp(): void {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false,
            'timeout' => 10
        ]);
    }

    /** @test */
    public function user_can_create_a_book_via_api(): void {
        $bookData = [
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'isbn' => 'ISBN-' . uniqid(),
            'publication_year' => 2008
        ];

        $response = $this->httpRequest('POST', '/books', $bookData);

        $this->assertEquals(201, $response['status']);
        $this->assertIsArray($response['body']);
        
        $expected = array_merge(
            ['id' => $response['body']['id']],
            $bookData,
            ['description' => $response['body']['description'] ?? null]
        );
        
        $this->assertEquals($expected, $response['body']);
        $this->assertArrayHasKey('Location', $response['headers']);
        $this->assertEquals("/books/{$response['body']['id']}", $response['headers']['Location']);
    }

    /** @test */
    public function user_can_list_all_books_via_api(): void {
        $this->httpRequest('POST', '/books', [
            'title' => '1984',
            'author' => 'George Orwell',
            'isbn' => 'ISBN-' . uniqid(),
            'publication_year' => 1949
        ]);

        $response = $this->httpRequest('GET', '/books');

        $this->assertEquals(200, $response['status']);
        $this->assertIsArray($response['body']);
        $this->assertGreaterThan(0, count($response['body']));
    }

    /** @test */
    public function user_can_search_books_by_title_via_api(): void {
        $this->httpRequest('POST', '/books', [
            'title' => 'Clean Architecture',
            'author' => 'Robert C. Martin',
            'isbn' => 'ISBN-' . uniqid(),
            'publication_year' => 2017
        ]);

        $response = $this->httpRequest('GET', '/books?title=Clean');

        $this->assertEquals(200, $response['status']);
        $this->assertIsArray($response['body']);
        $this->assertGreaterThan(0, count($response['body']));
        $this->assertStringContainsString('Clean', $response['body'][0]['title']);
    }

    /** @test */
    public function user_can_search_books_by_author_via_api(): void {
        $this->httpRequest('POST', '/books', [
            'title' => 'The Pragmatic Programmer',
            'author' => 'Andy Hunt',
            'isbn' => 'ISBN-' . uniqid(),
            'publication_year' => 1999
        ]);

        $response = $this->httpRequest('GET', '/books?author=Hunt');

        $this->assertEquals(200, $response['status']);
        $this->assertIsArray($response['body']);
        $this->assertGreaterThan(0, count($response['body']));
        $this->assertStringContainsString('Hunt', $response['body'][0]['author']);
    }

    /** @test */
    public function user_can_get_specific_book_via_api(): void {
        $createResponse = $this->httpRequest('POST', '/books', [
            'title' => 'Design Patterns',
            'author' => 'Gang of Four',
            'isbn' => 'ISBN-' . uniqid(),
            'publication_year' => 1994
        ]);

        $bookId = $createResponse['body']['id'];

        $response = $this->httpRequest('GET', "/books/{$bookId}");

        $this->assertEquals(200, $response['status']);
        $this->assertEquals($bookId, $response['body']['id']);
        $this->assertEquals('Design Patterns', $response['body']['title']);
    }

    /** @test */
    public function user_can_update_a_book_via_api(): void {
        $createResponse = $this->httpRequest('POST', '/books', [
            'title' => 'Original Title',
            'author' => 'Original Author',
            'isbn' => 'ISBN-' . uniqid(),
            'publication_year' => 2020
        ]);

        $bookId = $createResponse['body']['id'];

        $response = $this->httpRequest('PUT', "/books/{$bookId}", [
            'title' => 'Updated Title'
        ]);

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Updated Title', $response['body']['title']);
        $this->assertEquals('Original Author', $response['body']['author']);
    }

    /** @test */
    public function user_can_delete_a_book_via_api(): void {
        $createResponse = $this->httpRequest('POST', '/books', [
            'title' => 'Book to Delete',
            'author' => 'Test Author',
            'isbn' => 'ISBN-' . uniqid(),
            'publication_year' => 2023
        ]);

        $bookId = $createResponse['body']['id'];

        $response = $this->httpRequest('DELETE', "/books/{$bookId}");

        $this->assertEquals(204, $response['status']);
        $this->assertNull($response['body']);
        $getResponse = $this->httpRequest('GET', "/books/{$bookId}");
        $this->assertEquals(200, $getResponse['status']);
        $this->assertIsArray($getResponse['body']);
        $this->assertArrayHasKey('error', $getResponse['body']);
    }

    /** @test */
    public function api_supports_pagination(): void {
        for ($i = 1; $i <= 5; $i++) {
            $this->httpRequest('POST', '/books', [
                'title' => "Book $i",
                'author' => "Author $i",
                'isbn' => 'ISBN-' . uniqid(),
                'publication_year' => 2020 + $i
            ]);
        }

        $response = $this->httpRequest('GET', '/books?limit=2&offset=0');

        $this->assertEquals(200, $response['status']);
        $this->assertCount(2, $response['body']);
    }

    private function httpRequest(string $method, string $path, array $data = []): array {
        $options = [];
        
        if (!empty($data) && in_array($method, ['POST', 'PUT'])) {
            $options['json'] = $data;
        }
        
        $response = $this->client->request($method, $path, $options);
        
        $statusCode = $response->getStatusCode();
        $bodyContent = $response->getBody()->getContents();
        $body = empty($bodyContent) ? null : json_decode($bodyContent, true);
        
        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[$name] = $values[0];
        }
        
        return [
            'status' => $statusCode,
            'body' => $body,
            'headers' => $headers
        ];
    }
}

