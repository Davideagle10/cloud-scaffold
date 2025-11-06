#!/bin/bash
# Build all Lambda packages using Docker

set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}=== Building Lambda Packages using Docker ===${NC}\n"

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}Error: Docker is not running${NC}"
    echo "Please start Docker Desktop and try again"
    exit 1
fi

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Build Auth Service
echo -e "${YELLOW}[1/2] Building Auth Service...${NC}"
cd "$SCRIPT_DIR/backend/auth-service"
if [ -f "build-lambda-docker.sh" ]; then
    bash build-lambda-docker.sh
    echo -e "${GREEN}✓ Auth Service package built${NC}\n"
else
    echo -e "${RED}✗ build-lambda-docker.sh not found in auth-service${NC}"
    exit 1
fi

# Build Advise Service
echo -e "${YELLOW}[2/2] Building Advise Service...${NC}"
cd "$SCRIPT_DIR/backend/advise-service"
if [ -f "build-lambda-docker.sh" ]; then
    bash build-lambda-docker.sh
    echo -e "${GREEN}✓ Advise Service package built${NC}\n"
else
    echo -e "${RED}✗ build-lambda-docker.sh not found in advise-service${NC}"
    exit 1
fi

# Summary
cd "$SCRIPT_DIR"
echo -e "${GREEN}=== All Lambda Packages Built ===${NC}\n"
echo "Lambda build directories created:"
echo "  ✓ backend/auth-service/lambda-build/"
echo "  ✓ backend/advise-service/lambda-build/"
echo ""
echo "Package sizes:"
du -sh backend/auth-service/lambda-build/ backend/advise-service/lambda-build/ 2>/dev/null || true
echo ""
echo "Next steps:"
echo "  1. cd terraform"
echo "  2. terraform init"
echo "  3. terraform apply"
echo ""
echo -e "${YELLOW}Ready for Terraform deployment!${NC}"

