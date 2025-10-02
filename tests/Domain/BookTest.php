<?php

namespace BookManagement\Tests\Domain;

use BookManagement\Domain\Book;
use PHPUnit\Framework\TestCase;

class BookTest extends TestCase {
    /** @test */
    public function it_can_be_created_with_all_properties(): void {
        $book = new Book(
            1,
            'Clean Code',
            'Robert C. Martin',
            '0132350882',
            2008,
            'A handbook of agile software craftsmanship'
        );

        $this->assertEquals(1, $book->getId());
        $this->assertEquals('Clean Code', $book->getTitle());
        $this->assertEquals('Robert C. Martin', $book->getAuthor());
        $this->assertEquals('0132350882', $book->getIsbn());
        $this->assertEquals(2008, $book->getPublicationYear());
        $this->assertEquals('A handbook of agile software craftsmanship', $book->getDescription());
    }

    /** @test */
    public function it_can_be_created_without_id(): void {
        $book = new Book(
            null,
            'Clean Code',
            'Robert C. Martin',
            '0132350882',
            2008
        );

        $this->assertNull($book->getId());
    }

    /** @test */
    public function it_can_be_created_without_description(): void {
        $book = new Book(
            1,
            'Clean Code',
            'Robert C. Martin',
            '0132350882',
            2008
        );

        $this->assertNull($book->getDescription());
    }

    /** @test */
    public function it_can_be_converted_to_array(): void {
        $book = new Book(
            1,
            'Clean Code',
            'Robert C. Martin',
            '0132350882',
            2008,
            'Description'
        );

        $expected = [
            'id' => 1,
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'isbn' => '0132350882',
            'publication_year' => 2008,
            'description' => 'Description'
        ];

        $this->assertEquals($expected, $book->toArray());
    }

    /** @test */
    public function it_is_json_serializable(): void {
        $book = new Book(
            1,
            'Clean Code',
            'Robert C. Martin',
            '0132350882',
            2008,
            'Description'
        );

        $json = json_encode($book);
        $decoded = json_decode($json, true);

        $this->assertEquals(1, $decoded['id']);
        $this->assertEquals('Clean Code', $decoded['title']);
        $this->assertEquals('Robert C. Martin', $decoded['author']);
    }
}

