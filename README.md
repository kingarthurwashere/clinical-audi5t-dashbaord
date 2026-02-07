# Clinical Audit Data Collection System

## Overview
This system provides a complete solution for collecting and managing clinical audit data for Parirenyatwa RTC (Radiotherapy Centre) using XML as the database with an Object-Document Mapper (ODM) pattern.

## Architecture

### Components

1. **Frontend (HTML/JavaScript)**
   - `clinical-audit-form.html` - Data collection form
   - `dashboard.html` - Analytics and reporting dashboard

2. **Backend (PHP)**
   - `save-audit.php` - API endpoint for saving audit records
   - `statistics.php` - API endpoint for retrieving statistics
   - `export.php` - API endpoint for data export
   - `XmlOdm.php` - Object-Document Mapper class

3. **Database**
   - XML-based storage in `database/clinical_audits.xml`
   - Schema validation using `schema/clinical_audit.xsd`

## Installation

### Prerequisites
- PHP 7.4 or higher
- Web server (Apache/Nginx)
- Write permissions for database directory

### Setup Steps

1. **Extract files to your web server directory**
   ```bash
   /var/www/html/clinical-audit/
   ```

2. **Create database directory**
   ```bash
   mkdir -p database
   chmod 755 database
   ```

3. **Configure web server**
   - Ensure PHP is enabled
   - Set document root to the application directory

4. **Test installation**
   - Navigate to: `http://yourserver/clinical-audit/clinical-audit-form.html`

## Usage

### Data Collection

1. Open `clinical-audit-form.html` in your browser
2. Fill in the required fields (marked with red asterisk)
3. Click "Save to Database" button
4. System will generate unique record ID and confirm save

### Viewing Dashboard

1. Open `dashboard.html` in your browser
2. View statistics and analytics
3. Export data in JSON, CSV, or XML format

### API Endpoints

#### Save Record
```
POST /save-audit.php
Content-Type: application/json

{
  "patient-id": "RTC12345",
  "age": 45,
  "gender": "female",
  ...
}

Response:
{
  "success": true,
  "id": "AUD20240207_65c3f1234567",
  "message": "Audit record saved successfully"
}
```

#### Get Statistics
```
GET /statistics.php

Response:
{
  "success": true,
  "statistics": {
    "total_records": 150,
    "diagnosis_breakdown": {...},
    "gender_breakdown": {...},
    "avg_waiting_time": 32.5
  },
  "records": [...]
}
```

#### Export Data
```
GET /export.php?format=json
GET /export.php?format=csv
GET /export.php?format=xml
```

## XML ODM Features

### CRUD Operations

```php
require_once 'XmlOdm.php';

// Initialize
$odm = new XmlOdm('database/clinical_audits.xml');

// Create/Update
$record = new AuditRecord();
$record->setData($data);
$id = $odm->save($record);

// Read
$record = $odm->findById($id);
$records = $odm->findAll();
$records = $odm->findBy(['gender' => 'female']);

// Delete
$odm->delete($id);

// Statistics
$stats = $odm->getStatistics();

// Export
$json = $odm->export('json');
$csv = $odm->export('csv');
```

### Data Validation

The system uses XML Schema (XSD) for validation. To enable validation:

```php
// In XmlOdm.php, add validation method
public function validate() {
    $schema = 'schema/clinical_audit.xsd';
    return $this->dom->schemaValidate($schema);
}
```

## Database Structure

### XML Format
```xml
<?xml version="1.0" encoding="UTF-8"?>
<clinical_audit_database version="1.0" created="2024-02-07 10:00:00">
  <records>
    <record id="AUD20240207_65c3f1234567" 
            created_at="2024-02-07 10:15:00" 
            updated_at="2024-02-07 10:15:00">
      <field name="patient-id">RTC12345</field>
      <field name="age">45</field>
      <field name="gender">female</field>
      <field name="primary-diagnosis">breast-cancer</field>
      <!-- ... more fields ... -->
    </record>
  </records>
</clinical_audit_database>
```

## Security Considerations

1. **Input Validation**
   - All user input is sanitized using `htmlspecialchars()`
   - Required fields are validated on both client and server

2. **File Permissions**
   - Database directory should have restricted permissions (755)
   - Database file should be readable/writable by web server only

3. **Access Control**
   - Implement authentication for production use
   - Restrict API access to authorized users only

4. **Backup**
   ```php
   $backupFile = $odm->backup();
   ```

## Performance Optimization

### For Large Datasets

1. **Indexing** - Add index files for faster searches
2. **Caching** - Implement caching for statistics
3. **Pagination** - Limit records returned per request
4. **Database Migration** - Consider moving to MySQL/PostgreSQL for >10,000 records

### Recommended Limits
- XML works well for up to 5,000 records
- For larger datasets, consider traditional SQL database

## Troubleshooting

### Common Issues

1. **Database not saving**
   - Check directory permissions
   - Verify PHP has write access
   - Check error logs

2. **Form not submitting**
   - Check browser console for JavaScript errors
   - Verify API endpoint URL
   - Check CORS settings

3. **Statistics not loading**
   - Ensure database file exists
   - Check file permissions
   - Verify XML is well-formed

## Backup and Restore

### Backup
```bash
# Manual backup
cp database/clinical_audits.xml database/backup_$(date +%Y%m%d).xml

# Automated backup (cron)
0 2 * * * cp /path/to/database/clinical_audits.xml /path/to/backup/$(date +\%Y\%m\%d).xml
```

### Restore
```bash
cp database/backup_20240207.xml database/clinical_audits.xml
```

## Future Enhancements

1. User authentication and authorization
2. Multi-user support with role-based access
3. Real-time data validation
4. Advanced reporting and charts
5. Email notifications
6. Data import from external sources
7. Mobile app integration
8. RESTful API expansion

## Support

For issues or questions:
- Check the documentation
- Review error logs
- Contact system administrator

## License

Internal use only - Parirenyatwa Group of Hospitals

## Version History

- v1.0 (2024-02-07) - Initial release with XML ODM
