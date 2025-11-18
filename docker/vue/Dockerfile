# Dockerfile for Vue.js development with Vite
# This container runs the Vite development server for hot module reloading

FROM node:20-alpine

# Set working directory
WORKDIR /app

# Install dependencies first (for better caching)
COPY package*.json ./
RUN npm install

# Copy application files
COPY . .

# Expose Vite's default port
EXPOSE 5173

# Run Vite dev server with host flag for Docker networking
# --host allows external connections (required for Docker)
# --force forces dependency optimization even if cached
CMD ["npx", "vite", "dev", "--host", "--force"]
