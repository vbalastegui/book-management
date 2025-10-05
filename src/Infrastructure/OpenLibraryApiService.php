<?php

namespace BookManagement\Infrastructure;

use BookManagement\Application\BookApiServiceInterface;
use BookManagement\Application\OpenLibraryResponseParserInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class OpenLibraryApiService implements BookApiServiceInterface {
    private ClientInterface $client;
    private LoggerInterface $logger;
    private OpenLibraryResponseParserInterface $parser;
    private const BASE_URL = 'https://openlibrary.org/api/books';

    public function __construct(
        ClientInterface $client, 
        LoggerInterface $logger, 
        OpenLibraryResponseParserInterface $parser
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->parser = $parser;
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
