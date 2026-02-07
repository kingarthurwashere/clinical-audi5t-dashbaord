# Installation Guide
## Clinical Audit Data Collection System with XML Database

### Quick Start Guide

This system provides a complete clinical audit data collection solution using XML as the database with an Object-Document Mapper (ODM) pattern.

---

## 📋 System Requirements

- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 7.4 or higher (8.0+ recommended)
- **Extensions**: DOM, XML, libxml
- **Disk Space**: 100MB minimum
- **Permissions**: Write access for database directory

---

## 🚀 Installation Steps

### Step 1: Upload Files

Upload all files to your web server directory:

```
/var/www/html/clinical-audit/
├── clinical-audit-form.html
├── dashboard.html
├── save-audit.php
├── statistics.php
├── export.php
├── XmlOdm.php
├── config.php
├── test.php
├── README.md
├── database/
│   └── clinical_audits_sample.xml
└── schema/
    └── clinical_audit.xsd
```

### Step 2: Set Permissions

```bash
# Make database directory writable
chmod 755 database/
chmod 644 database/*.xml

# Make logs directory (optional)
mkdir logs
chmod 755 logs/
```

### Step 3: Test Installation

Run the test script to verify everything works:

```bash
php test.php
```

You should see:
```
=== Clinical Audit System Test ===
✓ ODM initialized successfully
✓ Created record: RTC10001
...
=== All Tests Completed Successfully ===
```

### Step 4: Access the System

Open in your web browser:
- **Data Entry Form**: `http://yourserver/clinical-audit/clinical-audit-form.html`
- **Dashboard**: `http://yourserver/clinical-audit/dashboard.html`

---

## 📝 File Descriptions

### Frontend Files

| File | Purpose |
|------|---------|
| `clinical-audit-form.html` | Main data collection form with validation |
| `dashboard.html` | Analytics dashboard with statistics and charts |

### Backend Files

| File | Purpose |
|------|---------|
| `save-audit.php` | API endpoint for saving records (POST) |
| `statistics.php` | API endpoint for retrieving statistics (GET) |
| `export.php` | API endpoint for data export (GET) |
| `XmlOdm.php` | Core Object-Document Mapper class |
| `config.php` | System configuration settings |

### Database Files

| File | Purpose |
|------|---------|
| `database/clinical_audits.xml` | Main XML database (auto-created) |
| `database/clinical_audits_sample.xml` | Sample data for reference |
| `schema/clinical_audit.xsd` | XML Schema for validation |

---

## 🔧 Configuration

Edit `config.php` to customize:

```php
// Database path
'path' => __DIR__ . '/database/clinical_audits.xml',

// Enable/disable features
'backup_enabled' => true,
'validation' => ['enabled' => true],

// Security settings
'require_authentication' => false, // Set true in production!
```

---

## 📊 Usage Guide

### Collecting Data

1. Open `clinical-audit-form.html`
2. Fill in required fields (marked with *)
3. Complete optional sections as needed
4. Click "Save to Database"
5. Confirmation message will show record ID

### Viewing Analytics

1. Open `dashboard.html`
2. View real-time statistics
3. Export data in JSON, CSV, or XML format
4. Click "Refresh Data" to update

### API Usage

**Save a Record:**
```bash
curl -X POST http://yourserver/clinical-audit/save-audit.php \
  -H "Content-Type: application/json" \
  -d '{
    "patient-id": "RTC12345",
    "age": 45,
    "gender": "female",
    ...
  }'
```

**Get Statistics:**
```bash
curl http://yourserver/clinical-audit/statistics.php
```

**Export Data:**
```bash
# JSON format
curl http://yourserver/clinical-audit/export.php?format=json -o export.json

# CSV format
curl http://yourserver/clinical-audit/export.php?format=csv -o export.csv
```

---

## 🔐 Security Recommendations

### For Production Use

1. **Enable Authentication**
   ```php
   // In config.php
   'require_authentication' => true,
   ```

2. **Restrict File Access**
   ```apache
   # In .htaccess
   <FilesMatch "\.(xml|xsd)$">
     Require all denied
   </FilesMatch>
   ```

3. **Use HTTPS**
   - Install SSL certificate
   - Force HTTPS redirect

4. **Set Strong Permissions**
   ```bash
   chmod 750 database/
   chmod 640 database/*.xml
   ```

5. **Restrict API Access**
   ```php
   'allowed_ips' => ['192.168.1.0/24'],
   ```

---

## 📁 Database Structure

### XML Format Example

```xml
<?xml version="1.0" encoding="UTF-8"?>
<clinical_audit_database version="1.0">
  <records>
    <record id="AUD20240207_123456" 
            created_at="2024-02-07 10:00:00" 
            updated_at="2024-02-07 10:00:00">
      <field name="patient-id">RTC12345</field>
      <field name="age">45</field>
      <field name="gender">female</field>
      <!-- ... more fields ... -->
    </record>
  </records>
</clinical_audit_database>
```

---

## 🛠️ Troubleshooting

### Common Issues

**Problem**: "Failed to save XML database"
- **Solution**: Check directory permissions: `chmod 755 database/`

**Problem**: Form not submitting
- **Solution**: Check browser console, verify PHP is working

**Problem**: Statistics not loading
- **Solution**: Ensure XML file exists and is readable

**Problem**: PHP errors
- **Solution**: Check error log: `tail -f /var/log/apache2/error.log`

### Debug Mode

Enable debug mode in `config.php`:
```php
'debug_mode' => true,
```

---

## 📈 Performance Tips

### For Large Datasets (1000+ records)

1. **Enable Caching**
   ```php
   'cache_enabled' => true,
   'cache_duration' => 300, // 5 minutes
   ```

2. **Implement Pagination**
   - Limit records per page
   - Add pagination controls

3. **Consider Migration**
   - For 10,000+ records, migrate to MySQL/PostgreSQL

---

## 🔄 Backup & Restore

### Manual Backup

```bash
# Backup database
cp database/clinical_audits.xml database/backup_$(date +%Y%m%d).xml

# Restore from backup
cp database/backup_20240207.xml database/clinical_audits.xml
```

### Automated Backup (Cron)

```bash
# Add to crontab
0 2 * * * cp /path/to/database/clinical_audits.xml /path/to/backups/$(date +\%Y\%m\%d).xml
```

### Programmatic Backup

```php
require_once 'XmlOdm.php';
$odm = new XmlOdm('database/clinical_audits.xml');
$backupFile = $odm->backup();
echo "Backup created: $backupFile";
```

---

## 📞 Support & Maintenance

### Regular Maintenance

- **Weekly**: Check database integrity
- **Monthly**: Create backups
- **Quarterly**: Review and archive old data

### Monitoring

Monitor these metrics:
- Database file size
- Number of records
- Average response time
- Error rate

---

## 🎯 Next Steps

1. ✅ Install and test the system
2. ✅ Customize configuration
3. ✅ Train users on data entry
4. ✅ Set up automated backups
5. ✅ Implement authentication (production)
6. ✅ Monitor and maintain

---

## 📚 Additional Resources

- **PHP XML Documentation**: https://www.php.net/manual/en/book.dom.php
- **XML Schema Tutorial**: https://www.w3schools.com/xml/schema_intro.asp
- **Security Best Practices**: See README.md

---

## ⚠️ Important Notes

- **Data Privacy**: Ensure compliance with HIPAA/GDPR
- **Regular Backups**: Database file can become corrupted
- **Access Control**: Implement proper authentication
- **Testing**: Test thoroughly before production use

---

## Version Information

- **Version**: 1.0.0
- **Release Date**: February 2024
- **PHP Version**: 7.4+
- **License**: Internal Use Only

---

**For technical support, contact your system administrator.**
