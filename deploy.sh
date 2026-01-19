#!/bin/bash

################################################################################
# Laravel Deployment Script for PA Penajam Website
# Version: 1.0
# Description: Automated deployment script with zero-downtime support
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="PA Penajam Website"
DEPLOY_USER="www-data"
DEPLOY_HOST="your-server.com"
DEPLOY_PATH="/var/www/pa-penajam"
GIT_REPO="git@github.com:pa-penajam/website.git"
GIT_BRANCH="production"
BACKUP_DIR="/var/backups/pa-penajam"
KEEP_RELEASES=5

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running on server
check_server() {
    if [ ! -d "$DEPLOY_PATH" ]; then
        log_error "Deployment path does not exist: $DEPLOY_PATH"
        exit 1
    fi
}

# Create backup
create_backup() {
    log_info "Creating backup..."

    BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="$BACKUP_DIR/$BACKUP_DATE"

    mkdir -p "$BACKUP_PATH"

    # Backup database
    log_info "Backing up database..."
    cd "$DEPLOY_PATH/current"
    php artisan backup:run --only-db --filename="$BACKUP_DATE-db.zip"

    # Backup .env file
    log_info "Backing up .env file..."
    cp "$DEPLOY_PATH/current/.env" "$BACKUP_PATH/.env"

    # Backup storage
    log_info "Backing up storage..."
    tar -czf "$BACKUP_PATH/storage.tar.gz" "$DEPLOY_PATH/current/storage/app" 2>/dev/null || true

    log_info "Backup completed: $BACKUP_PATH"
}

# Deploy new release
deploy_release() {
    log_info "Deploying new release..."

    RELEASE_DATE=$(date +%Y%m%d_%H%M%S)
    RELEASE_PATH="$DEPLOY_PATH/releases/$RELEASE_DATE"

    # Create release directory
    mkdir -p "$RELEASE_PATH"

    # Clone repository
    log_info "Cloning repository..."
    git clone --depth 1 -b "$GIT_BRANCH" "$GIT_REPO" "$RELEASE_PATH"

    # Link shared resources
    log_info "Linking shared resources..."
    ln -nfs "$DEPLOY_PATH/shared/storage" "$RELEASE_PATH/storage"
    ln -nfs "$DEPLOY_PATH/shared/.env" "$RELEASE_PATH/.env"

    # Install dependencies
    log_info "Installing PHP dependencies..."
    cd "$RELEASE_PATH"
    composer install --no-dev --optimize-autoloader --no-interaction

    log_info "Installing Node dependencies..."
    npm ci
    npm run build

    # Run migrations
    log_info "Running database migrations..."
    php artisan migrate --force

    # Optimize application
    log_info "Optimizing application..."
    php artisan optimize

    log_info "Release deployed: $RELEASE_PATH"
}

# Switch to new release
switch_release() {
    log_info "Switching to new release..."

    RELEASE_DATE=$(ls -t "$DEPLOY_PATH/releases" | head -1)
    RELEASE_PATH="$DEPLOY_PATH/releases/$RELEASE_DATE"

    # Update current symlink
    ln -nfs "$RELEASE_PATH" "$DEPLOY_PATH/current"

    log_info "Switched to: $RELEASE_PATH"
}

# Restart services
restart_services() {
    log_info "Restarting services..."

    # Reload PHP-FPM
    sudo systemctl reload php8.5-fpm

    # Restart queue workers
    sudo supervisorctl restart pa-penajam-queue:*

    # Clear OPCache
    php artisan opcache:clear

    log_info "Services restarted"
}

# Cleanup old releases
cleanup_releases() {
    log_info "Cleaning up old releases..."

    cd "$DEPLOY_PATH/releases"
    ls -t | tail -n +$(($KEEP_RELEASES + 1)) | xargs rm -rf

    log_info "Cleanup completed"
}

# Run health checks
health_check() {
    log_info "Running health checks..."

    # Check application health
    HEALTH_CHECK=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health)

    if [ "$HEALTH_CHECK" == "200" ]; then
        log_info "Health check passed"
    else
        log_error "Health check failed with status: $HEALTH_CHECK"
        exit 1
    fi

    # Test database connection
    php artisan db:show > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        log_info "Database connection OK"
    else
        log_error "Database connection failed"
        exit 1
    fi

    # Test cache connection
    php artisan cache:clear > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        log_info "Cache connection OK"
    else
        log_warn "Cache connection may have issues"
    fi
}

# Rollback on failure
rollback() {
    log_error "Deployment failed! Rolling back..."

    # Get previous release
    cd "$DEPLOY_PATH/releases"
    PREVIOUS_RELEASE=$(ls -t | sed -n '2p')

    if [ -z "$PREVIOUS_RELEASE" ]; then
        log_error "No previous release found!"
        exit 1
    fi

    log_info "Rolling back to: $PREVIOUS_RELEASE"

    # Switch to previous release
    ln -nfs "$DEPLOY_PATH/releases/$PREVIOUS_RELEASE" "$DEPLOY_PATH/current"

    # Restart services
    sudo systemctl reload php8.5-fpm
    sudo supervisorctl restart pa-penajam-queue:*

    log_error "Rollback completed"
}

# Main deployment flow
main() {
    log_info "Starting deployment for $PROJECT_NAME..."
    log_info "Branch: $GIT_BRANCH"
    log_info "Timestamp: $(date)"

    # Check prerequisites
    check_server

    # Create backup
    create_backup

    # Deploy new release
    deploy_release

    # Switch to new release
    switch_release

    # Restart services
    restart_services

    # Run health checks
    health_check

    # Cleanup old releases
    cleanup_releases

    log_info "Deployment completed successfully!"
}

# Trap errors and rollback
trap rollback ERR

# Run main deployment
main
