# Cloud Scaffold
# Language Advisory Platform

Web platform to request language advisory services.
This repository contains the full application: an Angular frontend, PHP microservices (packaged with Bref for AWS Lambda), infrastructure as code with Terraform, and utilities for testing and packaging.

![status](https://img.shields.io/badge/status-development-yellow) ![license](https://img.shields.io/badge/license-MIT-blue)

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Repository Structure](#repository-structure)
- [Requirements and Dependencies](#requirements-and-dependencies)
- [Local Development](#local-development)
- [Build and Packaging](#build-and-packaging)
- [Infrastructure and Deployment](#infrastructure-and-deployment)
- [CI/CD (Example)](#cicd-example)
- [Operation and Monitoring](#operation-and-monitoring)
- [MySQL → DynamoDB Migration (Brief Guide)](#mysql--dynamodb-migration-brief-guide)
- [Basic API References](#basic-api-references)
- [Security and Secrets](#security-and-secrets)
- [Testing](#testing)
- [Common Troubleshooting](#common-troubleshooting)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [Legacy Notes and Files](#legacy-notes-and-files)

## Overview

Language Advisory Platform is an Angular SPA that allows users to request linguistic advisory sessions.
The backend is composed of PHP microservices (Auth and Advise) packaged to run on AWS Lambda through Bref.
Infrastructure is declared with Terraform, using S3 + CloudFront for the frontend, API Gateway to expose the API, Lambdas for business logic, SNS for notifications, and DynamoDB for storage (the historical base was MySQL; migration notes are included).

## Architecture

**Simplified diagram:**

```
User (Angular SPA) --> CloudFront/S3
  └─> API Gateway (OpenAPI) -> Lambda (Auth Service)
                          -> Lambda (Advise Service) -> SNS -> subscribers (email / Lambda -> DynamoDB)
```

**Key Components:**

- **Frontend:** `frontend/` (Angular built artifacts in `dist/`)
- **Backend:** `backend/auth-service`, `backend/advise-service` (PHP + Bref)
- **Infrastructure:** `terraform/` (modules: `frontend`, `dynamodb`, `sns`, `lambdas`, `api-gateway`)
- **Scripts:** `scripts/prepare-lambdas.sh` (prepares `vendor/` for packaging)
- **Utilities:** `jules-scratch/` (Playwright tests and helper tools)

## Repository Structure

- `frontend/` — Angular source code.
- `backend/auth-service/` — authentication service (PHP + Bref).
- `backend/advise-service/` — request handling and SNS publishing service.
- `terraform/` — infrastructure modules and root configuration.
- `scripts/` — utility scripts; `prepare-lambdas.sh` prepares `vendor/` before packaging Lambdas.
- `jules-scratch/` — utilities and E2E tests (can be archived if unused).

## Requirements and Dependencies

**Local (development):**

- Node.js >= 16, npm or yarn
- Angular CLI (version aligned with the project)
- PHP >= 8.1 (CLI) and Composer
- Docker & Docker Compose (optional for containerized development)
- Python 3.8+ and Playwright (if using E2E tests)

**Infrastructure / deployment:**

- Terraform >= 1.0
- AWS CLI (optional)
- AWS credentials with permissions for S3, CloudFront, Lambda, IAM, SNS, DynamoDB
- Bref layer ARN (can be parameterized in `terraform/variables.tf`)

## Local Development

1. Clone the repo:

```bash
git clone <repo-url>
cd project
```

2. Environment variables (example `.env.example`):

```env
# Backend
APP_ENV=local
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloudscaffold
DB_USERNAME=root
DB_PASSWORD=secret

# SNS (if using localstack or mock)
SNS_TOPIC_ARN=arn:aws:sns:us-east-1:123456789012:cloudscaffold-topic

# Ports
FRONTEND_PORT=4200
API_PORT=8090
```

3. Run with Docker Compose (if using the full stack):

```bash
docker-compose up --build -d
```

4. Frontend development mode:

```bash
cd frontend
npm install
ng serve --host 0.0.0.0 --port $FRONTEND_PORT
```

5. Local backend (without Docker):

```bash
cd backend/auth-service
composer install
php -S 0.0.0.0:8090 -t public
```

6. Prepare Lambdas before Terraform (generates `vendor/` and packages dependencies):

```bash
./scripts/prepare-lambdas.sh
```

7. Run Playwright tests (if applicable):

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r jules-scratch/requirements.txt
python -m playwright install
python jules-scratch/verification/verify_dashboard.py
```

## Build and Packaging

**Frontend build:**

```bash
cd frontend
npm run build -- --prod
# artifacts in frontend/dist/
```

**Lambda packaging:** run `scripts/prepare-lambdas.sh` and let Terraform create the `.zip` with `data "archive_file"`.

## Infrastructure and Deployment (Terraform)

1. Initialize:

```bash
cd terraform
terraform init
terraform plan -var-file=terraform.tfvars
terraform apply -var-file=terraform.tfvars
```

2. Example `terraform.tfvars` (relevant fields):

```hcl
project_name = "cloud-scaffold"
environment  = "dev"
notification_email = "ops@example.com"
bref_php_layer = "arn:aws:lambda:us-east-1:212019880728:layer:php-81:5"
```

3. Remote backend recommendation:

Add an S3 + DynamoDB remote backend for state locking (example provided in docs). This prevents committing `terraform.tfstate` into the repository.

4. Deploy frontend to S3 + CloudFront invalidation:

```bash
aws s3 sync frontend/dist/ s3://my-bucket --delete
aws cloudfront create-invalidation --distribution-id XXXXXX --paths "/*"
```

## CI/CD (Example)

GitHub Actions: build frontend, deploy to S3; prepare Lambdas (composer install), create artifacts, and run `terraform plan`/`apply` using credentials stored in repository secrets.

It’s recommended to separate steps: plan on pull requests and apply on `main` branch with manual approval.

## Operation and Monitoring

Logs: CloudWatch Logs (Lambda). Search by function-name or RequestId.

Metrics: Lambda errors, latency, and invocations. Define alarms for error rate > X%.

SNS: verify active subscriptions and delivery failures.

## MySQL → DynamoDB Migration (Brief Guide)

1. Identify critical tables and relationships.

2. Create DynamoDB tables with suitable keys.

3. Write ETL scripts (export MySQL -> JSON -> PutItem into DynamoDB).

4. Update PHP controllers to use the AWS SDK DynamoDB client (add an abstraction layer to allow MySQL fallback during migration).

## Basic API References

**POST** `/api/requests` — create a request

Example request:

```json
{
  "user_id": "123",
  "language": "es",
  "message": "Necesito revisar un texto"
}
```

Example response (201):

```json
{ "id": "req_abc123", "status": "created" }
```

## Security and Secrets

Do not commit `.env` or `terraform.tfstate`.

Use AWS Secrets Manager or SSM Parameter Store for production credentials.

Apply IAM policies with the principle of least privilege (e.g., a Lambda publishing to SNS should have only `sns:Publish` permissions for that specific topic).

## Testing

Unit tests: (pending in backend) — add PHPUnit or equivalent.

E2E: Playwright (scripts in `jules-scratch/verification`).

## Common Troubleshooting

- `terraform init` fails: check versions, provider block, and malformed `.tf` or JSON/policy files.

- Bref layer not found: check `bref_php_layer` ARN.

- CORS: if frontend errors occur, review headers in Lambdas or API Gateway configuration.

## Contributing

Fork + feature branch `feat/my-change`.

Create PR against `master` with a description and test checklist.

**PR template (short):**

```md
- Brief description
- Changes made
- How to test
- Checklist: lint, tests, build
```

## Changelog

- `0.1.0` — Initial documentation with flow, local development, and deployment (date).

## Legacy Notes and Files

The repo contains Docker/Nginx artifacts and Kubernetes manifests from previous stages.
If migrating to serverless, move `kubernetes/`, `Docker/`, and `nginx/` to `archive/`.

`jules-scratch/` contains E2E utilities and tests; archive if not used in CI.

---

If you want, I can also add a `README-DEPLOY.md` with more detailed Terraform snippets and CI examples.


Overview
Language Advisory Platform is an Angular SPA that allows users to request linguistic advisory sessions.
The backend is composed of PHP microservices (Auth and Advise) packaged to run on AWS Lambda through Bref.
Infrastructure is declared with Terraform, using S3 + CloudFront for the frontend, API Gateway to expose the API, Lambdas for business logic, SNS for notifications, and DynamoDB for storage (the historical base was MySQL; migration notes are included).
Architecture
Simplified diagram:
User (Angular SPA) --> CloudFront/S3
  └─> API Gateway (OpenAPI) -> Lambda (Auth Service)
                          -> Lambda (Advise Service) -> SNS -> subscribers (email / Lambda -> DynamoDB)

Key Components:


Frontend: Angular (dist/)


Backend: backend/auth-service, backend/advise-service (PHP + Bref)


Infrastructure: terraform/ (modules: frontend, dynamodb, sns, lambdas, api-gateway)


Scripts: scripts/prepare-lambdas.sh (prepares vendor/ for packaging)


Utilities: jules-scratch/ (Playwright tests and helper tools)


Repository Structure


frontend/ — Angular source code.


backend/auth-service/ — authentication service (PHP + Bref).


backend/advise-service/ — request handling and SNS publishing service.


terraform/ — infrastructure modules and root configuration.


scripts/ — utility scripts; prepare-lambdas.sh prepares vendor/ before packaging Lambdas.


jules-scratch/ — utilities and E2E tests (can be archived if unused).


Requirements and Dependencies
Local (development):


Node.js >= 16, npm or yarn


Angular CLI (version aligned with the project)


PHP >= 8.1 (CLI) and Composer


Docker & Docker Compose (optional for containerized development)


Python 3.8+ and Playwright (if using E2E tests)


Infrastructure / deployment:


Terraform >= 1.0


AWS CLI (optional)


AWS credentials with permissions for S3, CloudFront, Lambda, IAM, SNS, DynamoDB


Bref layer ARN (can be parameterized in terraform/variables.tf)


Local Development


Clone the repo:


git clone <repo-url>
cd project



Environment variables (example .env.example):


# Backend
APP_ENV=local
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloudscaffold
DB_USERNAME=root
DB_PASSWORD=secret

# SNS (if using localstack or mock)
SNS_TOPIC_ARN=arn:aws:sns:us-east-1:123456789012:cloudscaffold-topic

# Ports
FRONTEND_PORT=4200
API_PORT=8090



Run with Docker Compose (if using the full stack):


docker-compose up --build -d



Frontend development mode:


cd frontend
npm install
ng serve --host 0.0.0.0 --port $FRONTEND_PORT



Local backend (without Docker):


cd backend/auth-service
composer install
php -S 0.0.0.0:8090 -t public



Prepare Lambdas before Terraform (generates vendor/ and packages dependencies):


./scripts/prepare-lambdas.sh



Run Playwright tests (if applicable):


python -m venv .venv
source .venv/bin/activate
pip install -r jules-scratch/requirements.txt
python -m playwright install
python jules-scratch/verification/verify_dashboard.py

Build and Packaging


Frontend build:


cd frontend
npm run build -- --prod
# artifacts in frontend/dist/



Lambda packaging: run scripts/prepare-lambdas.sh and let Terraform create the .zip with data "archive_file".


Infrastructure and Deployment (Terraform)


Initialize:


cd terraform
terraform init
terraform plan -var-file=terraform.tfvars
terraform apply -var-file=terraform.tfvars



Example terraform.tfvars (relevant fields):


project_name = "cloud-scaffold"
environment  = "dev"
notification_email = "ops@example.com"
bref_php_layer = "arn:aws:lambda:us-east-1:212019880728:layer:php-81:5"



Remote backend recommendation:


Add an S3 + DynamoDB remote backend for state locking (example provided in docs).
This prevents committing terraform.tfstate into the repository.


Deploy frontend to S3 + CloudFront invalidation:


aws s3 sync frontend/dist/ s3://my-bucket --delete
aws cloudfront create-invalidation --distribution-id XXXXXX --paths "/*"

CI/CD (Example)


GitHub Actions: build frontend, deploy to S3; prepare Lambdas (composer install), create artifacts, and run terraform plan/apply using credentials stored in repository secrets.


It’s recommended to separate steps: plan on pull requests and apply on main branch with manual approval.
Operation and Monitoring


Logs: CloudWatch Logs (Lambda). Search by function-name or RequestId.


Metrics: Lambda errors, latency, and invocations. Define alarms for error rate > X%.


SNS: verify active subscriptions and delivery failures.


MySQL → DynamoDB Migration (Brief Guide)


Identify critical tables and relationships.


Create DynamoDB tables with suitable keys.


Write ETL scripts (export MySQL -> JSON -> PutItem into DynamoDB).


Update PHP controllers to use the AWS SDK DynamoDB client (add an abstraction layer to allow MySQL fallback during migration).


Basic API References


POST /api/requests — create a request


Example request:
{
  "user_id": "123",
  "language": "es",
  "message": "Necesito revisar un texto"
}

Example response (201):
{ "id": "req_abc123", "status": "created" }

Security and Secrets


Do not commit .env or terraform.tfstate.


Use AWS Secrets Manager or SSM Parameter Store for production credentials.


Apply IAM policies with the principle of least privilege (e.g., a Lambda publishing to SNS should have only sns:Publish permissions for that specific topic).


Testing


Unit tests: (pending in backend) — add PHPUnit or equivalent.


E2E: Playwright (scripts in jules-scratch/verification).


Common Troubleshooting


terraform init fails: check versions, provider block, and malformed .tf or JSON/policy files.


Bref layer not found: check bref_php_layer ARN.


CORS: if frontend errors occur, review headers in Lambdas or API Gateway configuration.


Contributing


Fork + feature branch feat/my-change.


Create PR against master with a description and test checklist.


PR template (short):
- Brief description
- Changes made
- How to test
- Checklist: lint, tests, build

Changelog


0.1.0 — Initial documentation with flow, local development, and deployment (date).


Legacy Notes and Files


The repo contains Docker/Nginx artifacts and Kubernetes manifests from previous stages.
If migrating to serverless, move kubernetes/, Docker/, and nginx/ to archive/.


jules-scratch/ contains E2E utilities and tests; archive if not used in CI.





