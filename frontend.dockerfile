# Stage 1: Build the Angular application
FROM node:20-alpine AS build

# Set the working directory
WORKDIR /app

# Copy package.json and package-lock.json to leverage Docker cache
COPY frontend/package.json frontend/package-lock.json ./

# Install dependencies
RUN npm install

# Copy the rest of the application source code
COPY frontend/ .

# Build the application
RUN npm run dev

# Stage 2: Serve the application from Nginx
FROM nginx:alpine

# Copy the build output from the 'build' stage
COPY --from=build /app/dist/angular-app/browser /usr/share/nginx/html

# Copy the Nginx configuration
COPY docker/frontend.conf /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80