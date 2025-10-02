<?php

namespace BookManagement\Infrastructure;

use BookManagement\Application\BookApiServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class OpenLibraryApiService implements BookApiServiceInterface {
    private Client $client;
    private LoggerInterface $logger;
    private OpenLibraryResponseParser $parser;
    private const BASE_URL = 'https://openlibrary.org/api/books';

    public function __construct(?OpenLibraryResponseParser $parser = null) {
        $this->client = new Client();
        $this->logger = new Logger('OpenLibraryApiService');
        $this->logger->pushHandler(new StreamHandler('/var/www/html/logs/openlibrary.log', Logger::INFO));
        $this->parser = $parser ?? new OpenLibraryResponseParser();
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

            $this->logger->info("Successfully fetched book details for ISBN: $isbn");

            return $this->parser->parseBookData($bookData);
        } catch (RequestException $e) {
            $this->logger->error("API request failed: " . $e->getMessage());
            return null;
        }
    }
}
