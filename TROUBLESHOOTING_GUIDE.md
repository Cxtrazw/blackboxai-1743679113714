# Complaint System Troubleshooting Guide

## 1. Database Connection Issues
### Symptoms:
- "Connection failed" in diagnostic
- Complaints not saving

### Solutions:
```sql
-- Verify database exists
CREATE DATABASE IF NOT EXISTS acz_complaints;

-- Grant privileges (run in MySQL as root)
GRANT ALL PRIVILEGES ON acz_complaints.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

## 2. Missing Tables
### Symptoms:
- "complaints table does not exist" error

### Solutions:
```bash
# Import the SQL file via command line
mysql -u root -p acz_complaints < sql/setup_db.sql
```

## 3. Permission Issues
### Symptoms:
- "Access denied" errors
- Test insert fails

### Solutions:
```sql
-- Check user permissions
SHOW GRANTS FOR CURRENT_USER;

-- Fix permissions if needed
GRANT ALL PRIVILEGES ON acz_complaints.* TO 'root'@'localhost';
```

## 4. Data Not Saving
### Diagnostic Steps:
1. Run the diagnostic tool: http://localhost/api/db_diagnostic.php
2. Check XAMPP logs:
   - Apache: /xampp/apache/logs/error.log
   - MySQL: /xampp/mysql/data/mysql_error.log

## 5. Common Fixes
### If diagnostic shows table structure issues:
```sql
-- Example: Add missing column
ALTER TABLE complaints ADD COLUMN IF NOT EXISTS 
    submitted_by VARCHAR(50) NOT NULL AFTER priority;
```

## 6. Testing Credentials
Use these credentials to test:
- Admin: admin/password
- Facilities: facilities1/password
- Security: security1/password
- Operations: operations1/password

## 7. Debugging Process
1. First run db_diagnostic.php
2. Check error logs
3. Test with save_complaint_debug.php
4. Verify data directly in phpMyAdmin