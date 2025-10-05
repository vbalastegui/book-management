<?php

namespace BookManagement\Application;

interface OpenLibraryResponseParserInterface 
{
    /**
     * Parse book data from Open Library API response
     *
     * @param array $bookData Raw book data from API
     * @return array|null Parsed book details or null if parsing fails
     */
    public function parseBookData(array $bookData): ?array;
}
