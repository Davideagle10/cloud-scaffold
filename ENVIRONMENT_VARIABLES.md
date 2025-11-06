# Environment Variables Configuration

This document explains how environment variables are used in the microservices architecture.

## Database Configuration

The following environment variables are required for database connectivity:

- `DB_HOST`: Database server hostname (default: `db` for Docker, `localhost` for local development)
- `DB_PORT`: Database server port (default: `3306`)
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password

## Architecture Changes

### Shared Database.php

The `Database.php` file is now **shared** between all microservices:

- **Location**: `backend/app/core/Database.php`
- **Used by**: auth-service, advise-service, and any future services
- **Benefits**: Single source of truth, consistent database connection logic, easier maintenance

Individual services no longer contain their own `app/core/Database.php` files.

## How Environment Variables are Used

### 1. Docker Compose Setup

Environment variables are defined in `docker-compose.yml` and loaded from `.env` file:

```yaml
# .env file is automatically loaded by docker-compose
auth-service:
  environment:
    - DB_HOST=db
    - DB_PORT=3306
    - DB_NAME=${DB_NAME}    # Loaded from .env
    - DB_USER=${DB_USER}    # Loaded from .env
    - DB_PASS=${DB_PASS}    # Loaded from .env
```

### 2. Shared Database.php Class

The shared `backend/app/core/Database.php` class:

1. **First priority**: Read from container environment variables (`$_ENV` or `getenv()`)
2. **Fallback**: Read from `.env` file if environment variables are not set

This ensures that:
- Container environment variables take priority
- Local development can still use `.env` files
- The application works in both Docker and local environments
- All services use the same connection logic

### 3. Setup Instructions

#### For Docker Development:
1. Ensure `.env` file exists in project root (copy from `.env.example` if needed)
2. Run `docker-compose up --build`
3. Docker Compose automatically loads variables from `.env` file

#### For Local Development:
1. Set environment variables in your system, OR
2. Ensure `.env` file exists in `backend/` directory

### 4. Migration from .env File Dependency

**Before**: Services required `.env` files to be copied into containers
**After**: Services prioritize container environment variables, with `.env` as optional fallback

This change provides:
- Better containerization practices
- More flexible deployment options
- Easier environment-specific configuration
- Improved security (no need to copy sensitive files)

## Troubleshooting

If you encounter database connection issues:

1. Verify environment variables are set in `docker-compose.yml`
2. Check that `.env` file exists and contains correct values
3. Ensure database service is running and accessible
4. Check container logs for connection error details

## Security Notes

- Never commit `.env` files with sensitive data to version control
- Use `.env.example` as a template
- Consider using Docker secrets or external secret management for production