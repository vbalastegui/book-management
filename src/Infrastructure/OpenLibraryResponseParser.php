<?php

namespace BookManagement\Infrastructure;

class OpenLibraryResponseParser {
    
    public function parseBookData(array $bookData): array {
        return [
            'title' => $bookData['title'] ?? null,
            'author' => $this->extractAuthor($bookData),
            'description' => $this->extractDescription($bookData),
            'cover' => $bookData['cover']['large'] ?? null
        ];
    }

    private function extractDescription(array $bookData): ?string {
        $fields = ['description', 'notes', 'subtitle'];
        
        foreach ($fields as $field) {
            if (isset($bookData[$field])) {
                return $this->extractFieldValue($bookData[$field]);
            }
        }
        
        return null;
    }

    private function extractAuthor(array $bookData): ?string {
        return $bookData['authors'][0]['name'] ?? null;
    }

    private function extractFieldValue($field): ?string {
        if (is_array($field)) {
            return $field['value'] ?? null;
        }
        
        return $field;
    }
}

