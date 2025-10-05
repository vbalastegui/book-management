<?php

namespace BookManagement\Infrastructure;

use BookManagement\Application\OpenLibraryResponseParserInterface;

class OpenLibraryResponseParser implements OpenLibraryResponseParserInterface 
{
    public function parseBookData(array $bookData): ?array {
        $details = [
            'title' => $bookData['title'] ?? null,
            'author' => $this->extractAuthor($bookData),
            'description' => $this->extractDescription($bookData),
            'publication_year' => $this->extractPublicationYear($bookData)
        ];

        // Remove null values
        $details = array_filter($details);

        return !empty($details) ? $details : null;
    }

    private function extractAuthor(array $bookData): ?string {
        if (isset($bookData['authors']) && is_array($bookData['authors'])) {
            $authors = array_map(function($author) {
                return $author['name'] ?? null;
            }, $bookData['authors']);

            return implode(', ', array_filter($authors));
        }
        return null;
    }

    private function extractDescription(array $bookData): ?string {
        // Try multiple ways to extract description
        if (isset($bookData['description'])) {
            return is_array($bookData['description']) 
                ? $bookData['description']['value'] ?? null 
                : $bookData['description'];
        }
        
        if (isset($bookData['notes'])) {
            return is_array($bookData['notes']) 
                ? $bookData['notes']['value'] ?? null 
                : $bookData['notes'];
        }

        return null;
    }

    private function extractPublicationYear(array $bookData): ?int {
        if (isset($bookData['publish_date'])) {
            // Try to extract year from publish date
            preg_match('/\d{4}/', $bookData['publish_date'], $matches);
            return $matches[0] ?? null;
        }
        return null;
    }
}

