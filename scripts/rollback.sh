#!/bin/bash

################################################################################
# Laravel Rollback Script for PA Penajam Website
# Version: 1.0
# Description: Automated rollback script for quick recovery
################################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="PA Penajam Website"
DEPLOY_PATH="/var/www/pa-penajam"
CURRENT_RELEASE="$DEPLOY_PATH/current"

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

confirm() {
    read -p "$1 (y/n): " -n 1 -r
    echo
    [[ $REPLY =~ ^[Yy]$ ]]
}

# List available releases
list_releases() {
    log_info "Available releases:"
    echo "======================"

    cd "$DEPLOY_PATH/releases"

    ls -t | while read release; do
        if [ "$release" == "$(readlink $CURRENT_RELEASE | xargs basename)" ]; then
            echo -e "${GREEN}*$release (current)${NC}"
        else
            echo "  $release"
        fi
    done
}

# Rollback to previous release
rollback_to_previous() {
    log_info "Rolling back to previous release..."

    cd "$DEPLOY_PATH/releases"

    # Get current and previous releases
    CURRENT=$(readlink "$CURRENT_RELEASE" | xargs basename)
    PREVIOUS=$(ls -t | sed -n '2p')

    if [ -z "$PREVIOUS" ]; then
        log_error "No previous release found!"
        exit 1
    fi

    log_warn "Current release: $CURRENT"
    log_info "Rolling back to: $PREVIOUS"

    # Confirm rollback
    if confirm "Do you want to proceed?"; then
        # Switch to previous release
        ln -nfs "$DEPLOY_PATH/releases/$PREVIOUS" "$CURRENT_RELEASE"

        log_info "Release switched"
    else
        log_warn "Rollback cancelled"
        exit 0
    fi
}

# Rollback to specific release
rollback_to_specific() {
    if [ -z "$1" ]; then
        log_error "Please specify a release"
        exit 1
    fi

    RELEASE="$1"
    RELEASE_PATH="$DEPLOY_PATH/releases/$RELEASE"

    if [ ! -d "$RELEASE_PATH" ]; then
        log_error "Release not found: $RELEASE"
        exit 1
    fi

    log_warn "Rolling back to: $RELEASE"

    # Confirm rollback
    if confirm "Do you want to proceed?"; then
        # Switch to specific release
        ln -nfs "$RELEASE_PATH" "$CURRENT_RELEASE"

        log_info "Release switched"
    else
        log_warn "Rollback cancelled"
        exit 0
    fi
}

# Rollback database
rollback_database() {
    log_warn "Database rollback options:"
    echo "1) Rollback last migration batch"
    echo "2) Rollback to specific batch number"
    echo "3) Skip database rollback"

    read -p "Choose option [1-3]: " -n 1 -r
    echo

    case $REPLY in
        1)
            log_info "Rolling back last migration batch..."
            cd "$CURRENT_RELEASE"
            php artisan migrate:rollback
            ;;
        2)
            read -p "Enter batch number: " BATCH
            log_info "Rolling back to batch $BATCH..."
            cd "$CURRENT_RELEASE"
            php artisan migrate:rollback --step="$BATCH"
            ;;
        3)
            log_warn "Skipping database rollback"
            ;;
        *)
            log_error "Invalid option"
            exit 1
            ;;
    esac
}

# Restore backup
restore_backup() {
    log_warn "Backup restoration options:"
    echo "1) List available backups"
    echo "2) Restore from specific backup"
    echo "3) Skip backup restoration"

    read -p "Choose option [1-3]: " -n 1 -r
    echo

    case $REPLY in
        1)
            log_info "Available backups:"
            ls -lth /var/backups/pa-penajam/
            ;;
        2)
            read -p "Enter backup directory name: " BACKUP_DIR
            log_info "Restoring from: $BACKUP_DIR"
            # Add restoration logic here
            ;;
        3)
            log_warn "Skipping backup restoration"
            ;;
        *)
            log_error "Invalid option"
            exit 1
            ;;
    esac
}

# Restart services
restart_services() {
    log_info "Restarting services..."

    # Reload PHP-FPM
    sudo systemctl reload php8.5-fpm

    # Restart queue workers
    sudo supervisorctl restart pa-penajam-queue:*

    # Clear OPCache
    cd "$CURRENT_RELEASE"
    php artisan opcache:clear

    log_info "Services restarted"
}

# Clear caches
clear_caches() {
    log_info "Clearing caches..."

    cd "$CURRENT_RELEASE"

    php artisan optimize:clear
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear

    log_info "Caches cleared"
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
        log_warn "Please check the application manually"
    fi

    # Test database connection
    php artisan db:show > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        log_info "Database connection OK"
    else
        log_warn "Database connection may have issues"
    fi
}

# Main rollback flow
main() {
    log_info "Starting rollback for $PROJECT_NAME..."
    log_info "Timestamp: $(date)"

    # List releases
    list_releases

    # Ask which release to rollback to
    echo ""
    log_warn "Rollback options:"
    echo "1) Rollback to previous release"
    echo "2) Rollback to specific release"
    echo "3) Cancel"

    read -p "Choose option [1-3]: " -n 1 -r
    echo

    case $REPLY in
        1)
            rollback_to_previous
            ;;
        2)
            read -p "Enter release name: " RELEASE
            rollback_to_specific "$RELEASE"
            ;;
        3)
            log_warn "Rollback cancelled"
            exit 0
            ;;
        *)
            log_error "Invalid option"
            exit 1
            ;;
    esac

    # Database rollback
    echo ""
    if confirm "Do you want to rollback the database?"; then
        rollback_database
    fi

    # Backup restoration
    echo ""
    if confirm "Do you want to restore from backup?"; then
        restore_backup
    fi

    # Clear caches
    clear_caches

    # Restart services
    restart_services

    # Health checks
    health_check

    log_info "Rollback completed!"
    log_warn "Please verify the application is working correctly"
}

# Run main rollback
main
