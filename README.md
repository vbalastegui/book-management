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

## API Endpoints (RESTful)

| Method | Endpoint | Description | Query Params |
|--------|----------|-------------|--------------|
| `GET` | `/books` | List all books | `?limit=X&offset=Y` for pagination |
| `GET` | `/books?title=X` | Search books by title | `title` (string) |
| `GET` | `/books?author=X` | Search books by author | `author` (string) |
| `GET` | `/books/{id}` | Get a specific book | - |
| `POST` | `/books` | Create a new book | - |
| `PUT` | `/books/{id}` | Update a book | - |
| `DELETE` | `/books/{id}` | Delete a book | - |

### Example Usage
```bash
# List all books with pagination
curl "http://localhost:8080/books?limit=10&offset=0"

# Search by title (RESTful)
curl "http://localhost:8080/books?title=Clean"

# Search by author (RESTful)
curl "http://localhost:8080/books?author=Martin"

# Create a book
curl -X POST "http://localhost:8080/books" \
  -H "Content-Type: application/json" \
  -d '{"title":"Clean Code","author":"Robert C. Martin","isbn":"0132350882","publication_year":2008}'

# Get a specific book
curl "http://localhost:8080/books/1"

# Update a book
curl -X PUT "http://localhost:8080/books/1" \
  -H "Content-Type: application/json" \
  -d '{"title":"Updated Title"}'

# Delete a book
curl -X DELETE "http://localhost:8080/books/1"
```

## External API
Uses Open Library API for fetching book details by ISBN.

## Security Features
- Prepared SQL statements
- Basic JWT authentication
- Error logging
- Rate limiting for external API calls

## Project Structure
```
/src
  /Domain                  - Business logic and domain models
    /Criteria             - Criteria pattern for flexible queries
  /Application            - Application services (business logic)
  /Infrastructure         - External dependencies
    /Http                 - Controllers and routes (Slim Framework)
    /Di                   - Dependency injection configuration
/tests
  /Acceptance             - Acceptance tests
  /Unit                   - Unit tests
  /Integration            - Integration tests
/public                   - API entry point (index.php)
```

## Technologies Used
- **PHP 8.1+**: Core language
- **Slim Framework 4**: Microframework for routing and HTTP
- **PHP-DI**: Dependency injection container
- **SQLite**: Embedded database
- **Guzzle**: HTTP client for Open Library API
- **Monolog**: Logging
- **PHPUnit**: Testing framework

## Architecture
- **Hexagonal Architecture (Ports & Adapters)**
- **SOLID Principles**
- **Dependency Injection**
- **Criteria Pattern** for flexible repository queries
- **PSR-3** (Logger Interface)
- **PSR-7** (HTTP Message Interface)
- **PSR-11** (Container Interface)

## Additional Notes
- Full SOLID compliance
- Logger injection via DI (no hardcoded dependencies)
- Pagination by default (limit=100) for safety
- Prepared SQL statements (SQL injection protection)
- Comprehensive logging
