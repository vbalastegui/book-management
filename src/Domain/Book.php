<?php

namespace BookManagement\Domain;

class Book implements \JsonSerializable {
    private ?int $id;
    private string $title;
    private string $author;
    private string $isbn;
    private int $publicationYear;
    private ?string $description;

    public function __construct(
        ?int $id,
        string $title,
        string $author,
        string $isbn,
        int $publicationYear,
        ?string $description = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->publicationYear = $publicationYear;
        $this->description = $description;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getAuthor(): string {
        return $this->author;
    }

    public function getIsbn(): string {
        return $this->isbn;
    }

    public function getPublicationYear(): int {
        return $this->publicationYear;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    // Setters
    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    // Serialization
    public function toArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'publication_year' => $this->publicationYear,
            'description' => $this->description
        ];
    }

    public function jsonSerialize(): mixed {
        return $this->toArray();
    }
}
