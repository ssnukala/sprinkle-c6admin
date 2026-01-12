#!/bin/bash

##############################################################################
# Generate C6Admin Integration Test Paths from Schemas
#
# This script uses CRUD6's path generation framework to automatically create
# integration test paths from C6Admin's schema files.
#
# Usage:
#   ./generate-c6admin-test-paths.sh
#
# What it does:
#   1. Reads C6Admin schema files from app/schema/crud6/
#   2. Uses integration-test-models.json configuration
#   3. Generates complete integration-test-paths.json
#   4. Includes both authenticated and unauthenticated test paths
#
##############################################################################

set -e

# Color codes for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}C6Admin Test Path Generation${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Configuration files
MODELS_CONFIG="$PROJECT_ROOT/.github/config/integration-test-models.json"
OUTPUT_FILE="$PROJECT_ROOT/.github/config/integration-test-paths.json"
SCHEMA_DIR="$PROJECT_ROOT/app/schema/crud6"
GENERATOR_SCRIPT="$SCRIPT_DIR/generate-integration-test-paths.js"

echo -e "${BLUE}ℹ️  Configuration:${NC}"
echo "   Models config: $MODELS_CONFIG"
echo "   Schema directory: $SCHEMA_DIR"
echo "   Output file: $OUTPUT_FILE"
echo ""

# Check if Node.js is available
if ! command -v node &> /dev/null; then
    echo -e "${YELLOW}⚠️  Node.js not found. Please install Node.js to generate test paths.${NC}"
    exit 1
fi

# Check if schema directory exists
if [ ! -d "$SCHEMA_DIR" ]; then
    echo -e "${YELLOW}⚠️  Schema directory not found: $SCHEMA_DIR${NC}"
    exit 1
fi

# Check if generator script exists
if [ ! -f "$GENERATOR_SCRIPT" ]; then
    echo -e "${YELLOW}⚠️  Generator script not found: $GENERATOR_SCRIPT${NC}"
    echo "   Please ensure generate-integration-test-paths.js is in .github/scripts/"
    exit 1
fi

# Check if models config exists
if [ ! -f "$MODELS_CONFIG" ]; then
    echo -e "${YELLOW}⚠️  Models config not found: $MODELS_CONFIG${NC}"
    echo "   Please ensure integration-test-models.json is in .github/config/"
    exit 1
fi

# Count schema files
SCHEMA_COUNT=$(find "$SCHEMA_DIR" -name "*.json" -type f | wc -l)
echo -e "${GREEN}✅ Found $SCHEMA_COUNT schema files in $SCHEMA_DIR${NC}"
echo ""

# Run the generator
echo -e "${BLUE}🔄 Generating test paths from schemas...${NC}"
echo ""

# Run the Node.js script
node "$GENERATOR_SCRIPT" "$SCHEMA_DIR" "$OUTPUT_FILE" "$MODELS_CONFIG"

# Check if generation was successful
if [ -f "$OUTPUT_FILE" ]; then
    PATH_COUNT=$(grep -o '"path":' "$OUTPUT_FILE" | wc -l)
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}✅ Success!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo "Generated $PATH_COUNT test paths"
    echo "Output: $OUTPUT_FILE"
    echo ""
    echo -e "${BLUE}Next steps:${NC}"
    echo "1. Review generated paths in $OUTPUT_FILE"
    echo "2. Update .github/workflows/integration-test-modular.yml to use generated paths"
    echo "3. Run: php .github/scripts/test-paths.php .github/config/integration-test-paths.json"
    echo ""
else
    echo ""
    echo -e "${YELLOW}⚠️  Path generation may have failed. Check output above for errors.${NC}"
    exit 1
fi
