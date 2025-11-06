# Auth Service

This service is responsible for handling user authentication and registration.

## Endpoints

- `POST /api/v1/auth/login`: Authenticates a user and returns a JWT.
- `POST /api/v1/auth/register`: Registers a new user.

## Environment Variables

- `DB_DRIVER`: The database driver to use (`mysql` or `dynamodb`).
- `DB_HOST`: The hostname of the database server.
- `DB_NAME`: The name of the database.
- `DB_USER`: The username for the database.
- `DB_PASS`: The password for the database.
- `JWT_SECRET`: The secret key for signing JWTs.
