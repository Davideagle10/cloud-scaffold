# Advise Service

This service is responsible for handling advisory requests.

## Endpoints

- `POST /api/v1/requests`: Creates a new advisory request.
- `GET /api/v1/requests`: Retrieves all advisory requests.
- `GET /api/v1/requests/{id}`: Retrieves a single advisory request.
- `GET /api/v1/requests/summary/{id}`: Retrieves a summary of a single advisory request.
- `PUT /api/v1/requests/{id}/status`: Updates the status of an advisory request.
- `POST /api/v1/requests/correct-data`: Corrects a data field in an advisory request.

## Environment Variables

- `DB_DRIVER`: The database driver to use (`mysql` or `dynamodb`).
- `DB_HOST`: The hostname of the database server.
- `DB_NAME`: The name of the database.
- `DB_USER`: The username for the database.
- `DB_PASS`: The password for the database.
- `JWT_SECRET`: The secret key for verifying JWTs.
