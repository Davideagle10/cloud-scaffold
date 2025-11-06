# Backend - Language Advisory REST API

This directory contains the PHP-based REST API for the Language Advisory Platform. It is built using a modern, layered architecture to ensure separation of concerns and maintainability.

## Architecture

The API follows a classic layered architecture pattern:

-   **Controllers (`app/Controllers`):** Handle incoming HTTP requests, parse input, and return JSON responses. They are the entry point for API interactions and orchestrate the business logic.
-   **Services (`app/Services`):** Contain the core business logic. They are called by the controllers and use repositories to interact with the database. This layer is responsible for validation, data processing, and enforcing business rules.
-   **Repositories (`app/Repositories`):** Abstract the data access layer. They are responsible for all communication with the database, executing SQL queries, and mapping the results to data models. They use PDO for secure database interaction.
-   **Models (`app/Models`):** Simple entity classes that represent the data structures of the application (e.g., `Admin`, `Request`).
-   **Core (`app/Core`):** Contains foundational components like the secure database connection handler.

Dependency management and autoloading are handled by **Composer**.

---

## API Endpoints

The API provides the following endpoints, all prefixed with `/api`.

| Verbo  | URL                                | Descripción                                                               |
| :----- | :--------------------------------- | :------------------------------------------------------------------------ |
| `POST` | `/v1/auth/login`                   | Autentica a un administrador. Espera un JSON con `usuario` y `password`.  |
| `POST` | `/v1/requests`                     | Crea una nueva solicitud de asesoría. Espera un JSON con `nombre` y `correo`. |
| `GET`  | `/v1/requests`                     | Obtiene una lista de todas las solicitudes de asesoría para el dashboard. |
| `GET`  | `/v1/info/landing`                 | Proporciona información estática y general para la página de inicio.      |

---

## Local Execution

To run the backend service locally, you must use the `docker-compose.yml` file located in the project root.

1.  **Prerequisites:**
    *   Docker and Docker Compose must be installed.
    *   Ensure port `8080` is available on your local machine.

2.  **Configuration:**
    *   The project uses a `.env` file in the root directory for all configuration. A sample is provided.

3.  **Running the Service:**
    *   Navigate to the **root directory** of the project.
    *   Run the command: `docker-compose up -d --build`
    *   This will start the `app-backend` (PHP-FPM), `nginx-backend` (web server), and `db` (MySQL) services.

4.  **Accessing the API:**
    *   The API will be available at `http://localhost:8080/api`.
    *   You can test the endpoints using a tool like Postman or `curl`. For example: `curl http://localhost:8080/api/v1/info/landing`.

5.  **Running Tests:**
    *   To run the syntax checker, execute the following command:
        ```bash
        docker-compose exec app-backend-php composer test
        ```