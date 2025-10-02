<?php

namespace BookManagement\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class OpenLibraryApiService {
    private Client $client;
    private LoggerInterface $logger;
    private const BASE_URL = 'https://openlibrary.org/api/books';

    public function __construct() {
        $this->client = new Client();
        $this->logger = new Logger('OpenLibraryApiService');
        $this->logger->pushHandler(new StreamHandler('/var/www/html/logs/openlibrary.log', Logger::INFO));
    }

    public function fetchBookDetails(string $isbn): ?array {
        try {
            $response = $this->client->request('GET', self::BASE_URL, [
                'query' => [
                    'bibkeys' => "ISBN:$isbn",
                    'format' => 'json',
                    'jscmd' => 'data'
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (empty($data)) {
                $this->logger->warning("No book details found for ISBN: $isbn");
                return null;
            }

            $bookKey = "ISBN:$isbn";
            $bookData = $data[$bookKey] ?? null;

            if (!$bookData) {
                $this->logger->warning("Unexpected response format for ISBN: $isbn");
                return null;
            }

            return [
                'title' => $bookData['title'] ?? null,
                'author' => $bookData['authors'][0]['name'] ?? null,
                'description' => $bookData['description'] ?? null,
                'cover' => $bookData['cover']['large'] ?? null
            ];
        } catch (RequestException $e) {
            $this->logger->error("API request failed: " . $e->getMessage());
            return null;
        }
    }
}
