# Prueba técnica de PHP nativo

## Título del ejercicio:
Gestión de libros con API externa y principios avanzados de desarrollo

## Descripción General:
Se trata de crear una pequeña aplicación que gestione una lista de libros, permitiendo realizar operaciones CRUD (Crear, Leer, Actualizar, Eliminar) y consultar una API externa para obtener información adicional sobre los libros (por ejemplo, utilizando la API de Open Library o Google Books).

Los participantes deben diseñar esta aplicación usando PHP nativo y aplicar los principios de arquitectura y desarrollo solicitados en el documento.

## Requisitos funcionales:
1. **CRUD de libros**: El sistema debe permitir gestionar una lista de libros con la siguiente información:
   • Título
   • Autor
   • ISBN
   • Año de publicación
   • Descripción (obtenida de una API externa)

2. **Conexión con API externa**: Al agregar o actualizar un libro, el sistema debe realizar una consulta a una API externa para obtener información adicional sobre el libro, como la descripción y la portada. Usa una API pública como Open Library API.

3. **Búsqueda de libros**: Permitir a los usuarios buscar libros por título o autor en la base de datos local.

## Requisitos Técnicos:
1. **PHP nativo**: El código debe ser implementado sin frameworks, aunque se permite el uso de librerías o microframework si es necesario (por ejemplo, Guzzle para realizar peticiones HTTP, Slim como microframework).

2. **Pruebas automáticas**: Implementar un conjunto básico de pruebas unitarias y de integración usando PHPUnit.

3. **Conexión con base de datos**: Se debe implementar la conexión a una base de datos MySQL o SQLite para simplificar.

4. **Implementación de SOLID**: Se valorará que el código esté bien estructurado y siga los principios SOLID (responsabilidad única, inyección de dependencias, etc.).

5. **Uso de al menos un patrón de diseño**: Debes implementar al menos un patrón de diseño (Singleton, Factory, etc.) para demostrar una buena arquitectura de software.

6. **Manejo de errores**: El sistema debe manejar adecuadamente errores y excepciones, incluyendo problemas de conectividad con la API externa, devolviendo respuestas claras al usuario y utilizando códigos HTTP correctos (400, 500, etc.).

7. **Logging básico**: Implementar un sistema de logs para registrar eventos clave como errores o acciones importantes (creación de libros, actualizaciones, consultas a la API externa, etc.).

8. **Seguridad**: Proteger la aplicación contra ataques como inyección SQL (usando consultas preparadas), XSS y CSRF. Implementar autenticación básica o mediante tokens (JWT o similar).

9. **Docker**: Crear un contenedor Docker para facilitar el despliegue de la aplicación, con un Dockerfile que contenga las configuraciones de PHP y MySQL/SQLite.
## Extras que se valorarán adicionalmente:

1. **Caching**: Implementar mecanismos de cacheo (por ejemplo, para resultados de la API externa) usando archivos o un sistema como Redis.

2. **CQRS**: Separar los comandos (crear, actualizar, eliminar) de las consultas (leer libros) siguiendo el patrón CQRS.

3. **Domain-Driven Design (DDD)**: Organizar la lógica de negocio en capas claras siguiendo los principios de Domain-Driven Design.

4. **Internacionalización (i18n)**: Añadir soporte para múltiples idiomas en la interfaz o los mensajes de respuesta de la API.

5. **Optimización de la Base de Datos**: Implementar índices en la base de datos para optimizar búsquedas por campos clave como título o autor.

6. **Escalabilidad y recomendaciones**: Incluir sugerencias sobre cómo escalar la aplicación en un entorno de producción. Por ejemplo, mejoras para manejar grandes volúmenes de datos o alta concurrencia.

7. **Rate Limiting**: Implementar un control de la tasa de peticiones a la API externa para evitar exceder los límites de la API.

8. **Documentación de la API**: Crear documentación de la API utilizando herramientas como Swagger o describirla en el archivo README.md.


## Pasos para la evaluación:

1. **Desarrollo del backend**: Los participantes deben entregar un proyecto de PHP nativo que cumpla con los requisitos funcionales.

2. **Conexión con API externa**: Se debe evaluar cómo gestionan las llamadas a la API externa y cómo almacenan/cachan los datos obtenidos.

3. **Pruebas automáticas**: Ejecutar el set de pruebas unitarias e integrales que hayan implementado para verificar el correcto funcionamiento de la lógica de negocio.

4. **Evaluar arquitectura y patrones**: Revisar el código en busca de implementación de patrones de diseño, adherencia a SOLID y buenas prácticas de arquitectura (como CQRS y DDD, si están presentes).

5. **Seguridad**: Probar la aplicación contra ataques básicos de seguridad (inyección SQL, XSS) y verificar si la autenticación está implementada correctamente.

6. **Docker**: Verificar si el proyecto corre correctamente dentro de un contenedor Docker.
## Estructura recomendada del proyecto:

```
• /src
  – /Domain (Lógica de negocio y modelos de dominio)
  – /Infrastructure (Gestión de base de datos, conexión API externa)
  – /Application (Controladores, manejo de lógica de aplicación)
  – /Tests (Pruebas unitarias e integración)
• /public (punto de entrada para la API)
  – index.php
• Dockerfile
• docker-compose.yml
• composer.json (para gestionar dependencias como PHPUnit)
```

## Extra: API sugerida

Para la API externa, puedes usar la Open Library API, que ofrece una manera sencilla de obtener datos de libros mediante consultas por ISBN, título o autor.

**Ejemplo de llamada a la API:**

```php
$client = new \GuzzleHttp\Client();
$response = $client->request('GET', 'https://openlibrary.org/api/books', [
    'query' => [
        'bibkeys' => 'ISBN:0451526538',
        'format' => 'json',
        'jscmd' => 'data'
    ]
]);
$bookData = json_decode($response->getBody(), true);
```

## Entrega:

Los participantes deben entregar:

• El código fuente en un repositorio de Git.
• Un archivo README.md con instrucciones para correr el proyecto y los tests (incluyendo la configuración de Docker si aplica).
• Capturas o logs de los tests ejecutados correctamente.
