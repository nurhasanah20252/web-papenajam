#!/bin/bash

################################################################################
# Laravel Backup Script for PA Penajam Website
# Version: 1.0
# Description: Automated backup script for database and files
################################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="PA Penajam Website"
APP_PATH="/var/www/pa-penajam/current"
BACKUP_DIR="/var/backups/pa-penajam"
RETENTION_DAYS=30

# Database configuration (from .env)
DB_DATABASE=$(grep "^DB_DATABASE=" "$APP_PATH/.env" | cut -d '=' -f2)
DB_USERNAME=$(grep "^DB_USERNAME=" "$APP_PATH/.env" | cut -d '=' -f2)
DB_PASSWORD=$(grep "^DB_PASSWORD=" "$APP_PATH/.env" | cut -d '=' -f2)
DB_HOST=$(grep "^DB_HOST=" "$APP_PATH/.env" | cut -d '=' -f2)

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

# Create backup directory
create_backup_dir() {
    BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="$BACKUP_DIR/$BACKUP_DATE"
    mkdir -p "$BACKUP_PATH"
    log_info "Backup directory created: $BACKUP_PATH"
}

# Backup database
backup_database() {
    log_info "Backing up database: $DB_DATABASE"

    # MySQL dump
    mysqldump -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" \
        --single-transaction \
        --quick \
        --lock-tables=false \
        "$DB_DATABASE" | gzip > "$BACKUP_PATH/database.sql.gz"

    # Backup size
    BACKUP_SIZE=$(du -h "$BACKUP_PATH/database.sql.gz" | cut -f1)
    log_info "Database backup completed: $BACKUP_SIZE"
}

# Backup files
backup_files() {
    log_info "Backing up files..."

    # Backup storage/app directory
    tar -czf "$BACKUP_PATH/storage.tar.gz" \
        -C "$APP_PATH" \
        storage/app 2>/dev/null || true

    # Backup .env file
    cp "$APP_PATH/.env" "$BACKUP_PATH/.env"

    # Backup public uploads (if any)
    if [ -d "$APP_PATH/public/uploads" ]; then
        tar -czf "$BACKUP_PATH/uploads.tar.gz" \
            -C "$APP_PATH" \
            public/uploads 2>/dev/null || true
    fi

    log_info "Files backup completed"
}

# Upload to S3 (optional)
upload_to_s3() {
    if grep -q "^BACKUP_DISK=s3" "$APP_PATH/.env"; then
        log_info "Uploading to S3..."

        # Use Laravel backup command
        cd "$APP_PATH"
        php artisan backup:run --only-db

        log_info "S3 upload completed"
    else
        log_warn "S3 backup not configured"
    fi
}

# Cleanup old backups
cleanup_old_backups() {
    log_info "Cleaning up old backups (older than $RETENTION_DAYS days)..."

    find "$BACKUP_DIR" -type d -mtime +$RETENTION_DAYS -exec rm -rf {} \;

    log_info "Cleanup completed"
}

# Create backup manifest
create_manifest() {
    log_info "Creating backup manifest..."

    cat > "$BACKUP_PATH/manifest.txt" <<EOF
Backup Manifest for $PROJECT_NAME
=====================================
Backup Date: $(date)
Server: $(hostname)
Release: $(cd "$APP_PATH" && git log -1 --format="%h %s")
Database: $DB_DATABASE
Files:
  - database.sql.gz
  - storage.tar.gz
  - .env
EOF

    log_info "Manifest created"
}

# Main backup flow
main() {
    log_info "Starting backup for $PROJECT_NAME..."
    log_info "Timestamp: $(date)"

    # Create backup directory
    create_backup_dir

    # Backup database
    backup_database

    # Backup files
    backup_files

    # Upload to S3 (if configured)
    upload_to_s3

    # Create manifest
    create_manifest

    # Cleanup old backups
    cleanup_old_backups

    log_info "Backup completed successfully!"
    log_info "Backup location: $BACKUP_PATH"
}

# Run main backup
main
