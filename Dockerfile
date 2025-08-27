# Dockerfile for Beehive Lodge Website
# Multi-stage build for optimized production deployment

# Stage 1: Build stage (for any build processes if needed)
FROM node:18-alpine AS builder

WORKDIR /app

# Copy package files (if you have any build processes)
# COPY package*.json ./
# RUN npm ci --only=production

# For this static site, we'll just copy files
COPY . .

# Stage 2: Production stage with Nginx
FROM nginx:alpine

# Install additional tools for maintenance
RUN apk add --no-cache \
    curl \
    tzdata \
    && rm -rf /var/cache/apk/*

# Set timezone
ENV TZ=Europe/Paris
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Copy custom Nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/conf.d/default.conf

# Copy website files from builder stage
COPY --from=builder /app /usr/share/nginx/html

# Remove unnecessary files from the web root
RUN rm -rf /usr/share/nginx/html/node_modules \
           /usr/share/nginx/html/Dockerfile* \
           /usr/share/nginx/html/.git* \
           /usr/share/nginx/html/README.md

# Create logs directory
RUN mkdir -p /usr/share/nginx/html/logs && \
    chmod 755 /usr/share/nginx/html/logs

# Add custom error pages
COPY error-pages/ /usr/share/nginx/html/error-pages/

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Create health check endpoint
RUN echo '<!DOCTYPE html><html><head><title>Health Check</title></head><body><h1>OK</h1><p>Beehive Lodge is running</p></body></html>' > /usr/share/nginx/html/health

# Set proper permissions
RUN chown -R nginx:nginx /usr/share/nginx/html && \
    chmod -R 755 /usr/share/nginx/html

# Expose port 80
EXPOSE 80

# Labels for better container management
LABEL maintainer="Beehive Lodge <admin@beechivelodge.com>"
LABEL description="Beehive Lodge luxury accommodation website"
LABEL version="1.0"
LABEL build-date="2024-11-26"

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
