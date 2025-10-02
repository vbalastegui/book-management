# Book Management Technical Test

## Project Description
A PHP native book management application that allows CRUD operations on books and fetches additional book information from the Open Library API.

## Requirements
- Docker
- Docker Compose
- PHP 8.1+
- Composer

## Setup and Installation

1. Clone the repository
```bash
git clone https://github.com/yourusername/book-management.git
cd book-management
```

2. Install dependencies
```bash
composer install
```

3. Build and run the Docker containers
```bash
docker-compose up --build
```

The application will be available at `http://localhost:8080`

## Running Tests
```bash
docker-compose run app composer test
```

## API Endpoints
- `GET /books`: List all books
- `GET /books/{id}`: Get a specific book
- `POST /books`: Create a new book
- `PUT /books/{id}`: Update a book
- `DELETE /books/{id}`: Delete a book

## External API
Uses Open Library API for fetching book details by ISBN.

## Security Features
- Prepared SQL statements
- Basic JWT authentication
- Error logging
- Rate limiting for external API calls

## Project Structure
- `/src/Domain`: Business logic and domain models
- `/src/Infrastructure`: Database and external API interactions
- `/src/Application`: Controllers and application logic
- `/src/Tests`: Unit and integration tests
- `/public`: API entry point

## Additional Notes
- Follows SOLID principles
- Implements CQRS pattern
- Includes basic logging
- Docker containerization
