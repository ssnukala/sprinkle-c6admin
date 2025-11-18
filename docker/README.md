# Docker Configuration for C6Admin Development

This directory contains Docker configuration files for local development and testing of the C6Admin sprinkle.

## Files

- **vue/Dockerfile**: Dockerfile for running the Vite development server
- **../docker-compose.yml**: Docker Compose configuration for the full development environment

## Usage

### Using Docker Compose (Recommended)

Run the Vite development server:

```bash
docker-compose up vite
```

This will:
- Build the Vue.js development environment
- Start the Vite dev server on port 5173
- Enable hot module reloading
- Mount your local source code for live updates

### Using Docker Directly

Build the image:

```bash
docker build -t c6admin-vite -f docker/vue/Dockerfile .
```

Run the container:

```bash
docker run -p 5173:5173 -v $(pwd)/app/assets:/app/app/assets c6admin-vite
```

## Configuration

The Vite server is started with the following flags:

- `--host`: Allows connections from outside the container (required for Docker)
- `--force`: Forces dependency optimization even if cached

## Integration with UserFrosting

This Docker configuration is designed to work alongside a UserFrosting 6 installation. The Vite server handles hot module reloading for frontend assets while UserFrosting's PHP server handles the backend.

## Notes

- The Vite server runs on port 5173 by default
- Source code is mounted as a volume for hot reloading
- Node modules are excluded from the volume mount to prevent conflicts
- This configuration is for development only, not production

## Troubleshooting

If the Vite server fails to start:

1. Check the logs: `docker-compose logs vite`
2. Verify port 5173 is not already in use
3. Ensure all dependencies are properly installed in package.json
4. Try rebuilding the image: `docker-compose build --no-cache vite`

## CI/CD Integration

The integration test workflow uses the UserFrosting bakery command (`php bakery assets:vite`) instead of Docker. This Docker setup is primarily for local development.
