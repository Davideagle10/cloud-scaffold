# Backend Proposal for Data Correction

This document outlines the requirements for the new backend endpoint that handles data correction for a request record.

## Endpoint

*   **URL:** `/v1/requests/correct-data`
*   **Method:** `POST`

## Request Body (JSON)

The endpoint should expect a JSON payload with the following structure:

```json
{
  "requestId": 123,
  "campoACorregir": "email",
  "valorAnterior": "old_value",
  "valorNuevo": "new_value"
}
```

*   `requestId`: The ID of the main request record to be updated.
*   `campoACorregir`: The name of the field to be corrected (e.g., 'email', 'telefono', 'nombre').
*   `valorAnterior`: The previous value of the field. This should be verified against the current value in the database before updating.
*   `valorNuevo`: The new value to be set for the field.

## Backend Logic

The PHP backend should perform the following steps:

1.  **Validate Input:**
    *   Receive the POST request and decode the JSON body.
    *   Validate that all required fields (`requestId`, `campoACorregir`, `valorAnterior`, `valorNuevo`) are present and have the correct data types.
    *   Ensure `valorNuevo` is not empty.

2.  **Verify `valorAnterior`:**
    *   Query the `requests` table to fetch the current value of the `campoACorregir` for the given `requestId`.
    *   Compare the fetched value with the `valorAnterior` from the request payload. If they do not match, return an error response to prevent race conditions or stale data updates.

3.  **Create Audit Record:**
    *   Insert a new record into a `data_corrections` audit table. This table should store the history of all data corrections.
    *   The `data_corrections` table should have the following columns (at a minimum):
        *   `id` (Primary Key)
        *   `request_id` (Foreign Key to `requests.id`)
        *   `field_corrected`
        *   `old_value`
        *   `new_value`
        *   `corrected_at` (Timestamp)
        *   `corrected_by` (User ID, if applicable)

4.  **Update Request Record:**
    *   Update the `requests` table, setting the `campoACorregir` to the `valorNuevo` for the given `requestId`.

5.  **Return Response:**
    *   If all steps are successful, return a `200 OK` response with a success message.
    *   If any step fails, return an appropriate error response (e.g., `400 Bad Request` for invalid input, `409 Conflict` for mismatched `valorAnterior`, `500 Internal Server Error` for database errors).

## Database Schema (Proposed)

### `data_corrections` table

```sql
CREATE TABLE data_corrections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    field_corrected VARCHAR(255) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    corrected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- corrected_by INT, -- Optional: If you have user authentication
    FOREIGN KEY (request_id) REFERENCES requests(id)
);
```