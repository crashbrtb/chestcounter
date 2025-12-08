#!/bin/bash

###############################################################################
# MySQL/MariaDB Database Backup Script
# Keeps backups from the last 7 days
# Usage: ./backup_database.sh
###############################################################################

# ============================================================================
# DATABASE CONFIGURATIONS - FILL IN WITH YOUR DATA
# ============================================================================
DB_HOST="localhost"
DB_PORT="3306"
DB_USER="seu_usuario"
DB_PASS="sua_senha"
DB_NAME="seu_banco"

# ============================================================================
# BACKUP CONFIGURATIONS
# ============================================================================
BACKUP_DIR="$HOME/bkp_db"
DAYS_TO_KEEP=7

# ============================================================================
# SCRIPT CODE - DO NOT MODIFY BELOW
# ============================================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Date and time for the filename
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_FILE="$BACKUP_DIR/backup_${DB_NAME}_${DATE}.sql"

# Log file
LOG_FILE="$BACKUP_DIR/backup.log"

# Log function
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
    echo "$1"
}

log_message "=========================================="
log_message "Starting database backup: $DB_NAME"

# Check if mysqldump is available
if ! command -v mysqldump &> /dev/null; then
    log_message "${RED}Error: mysqldump not found. Please install MySQL client.${NC}"
    exit 1
fi

# Perform the backup
log_message "Running mysqldump..."

# Capture errors in temporary variable
ERROR_OUTPUT=$(mktemp)
EXIT_CODE=0

if [ -n "$DB_PASS" ]; then
    # With password
    mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2> "$ERROR_OUTPUT"
    EXIT_CODE=$?
else
    # Without password (uses .my.cnf file or prompt)
    mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" > "$BACKUP_FILE" 2> "$ERROR_OUTPUT"
    EXIT_CODE=$?
fi

# Check if there was an error and show message
if [ $EXIT_CODE -ne 0 ]; then
    ERROR_MSG=$(cat "$ERROR_OUTPUT")
    log_message "${RED}Error executing mysqldump!${NC}"
    log_message "${RED}Error details:${NC}"
    echo "$ERROR_MSG" | while IFS= read -r line; do
        log_message "${RED}  $line${NC}"
    done
    echo "$ERROR_MSG" >> "$LOG_FILE"
    rm -f "$ERROR_OUTPUT"
    [ -f "$BACKUP_FILE" ] && rm -f "$BACKUP_FILE"
    exit 1
fi

rm -f "$ERROR_OUTPUT"

# Check if backup was created successfully
if [ -f "$BACKUP_FILE" ] && [ -s "$BACKUP_FILE" ]; then
    # Compress the backup
    log_message "Compressing backup..."
    gzip "$BACKUP_FILE"
    BACKUP_FILE="${BACKUP_FILE}.gz"
    
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    log_message "${GREEN}Backup created successfully: $BACKUP_FILE (Size: $BACKUP_SIZE)${NC}"
else
    log_message "${RED}Error: Backup file was not created or is empty!${NC}"
    [ -f "$BACKUP_FILE" ] && rm -f "$BACKUP_FILE"
    exit 1
fi

# Clean up old backups (older than 7 days)
log_message "Cleaning up old backups (older than $DAYS_TO_KEEP days)..."
DELETED_COUNT=0

# Find and delete files older than X days
while IFS= read -r -d '' file; do
    if [ -f "$file" ]; then
        rm -f "$file"
        DELETED_COUNT=$((DELETED_COUNT + 1))
        log_message "Removed: $(basename "$file")"
    fi
done < <(find "$BACKUP_DIR" -name "backup_${DB_NAME}_*.sql.gz" -type f -mtime +$DAYS_TO_KEEP -print0 2>/dev/null)

if [ $DELETED_COUNT -gt 0 ]; then
    log_message "${YELLOW}Removed $DELETED_COUNT old backup(s)${NC}"
else
    log_message "No old backups found to remove"
fi

# List current backups
CURRENT_BACKUPS=$(find "$BACKUP_DIR" -name "backup_${DB_NAME}_*.sql.gz" -type f | wc -l)
log_message "Total backups kept: $CURRENT_BACKUPS"

log_message "Backup completed successfully!"
log_message "=========================================="

exit 0
