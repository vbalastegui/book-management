<?php

namespace BookManagement\Application;

interface BookApiServiceInterface {
    /**
     * Fetch book details from external API by ISBN
     * 
     * @param string $isbn
     * @return array|null Book details (title, author, description, cover) or null if not found
     */
    public function fetchBookDetails(string $isbn): ?array;
}

