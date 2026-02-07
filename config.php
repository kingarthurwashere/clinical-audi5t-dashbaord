<?php
/**
 * Configuration File
 * System-wide settings for Clinical Audit System
 */

return [
    // Database Configuration
    'database' => [
        'type' => 'xml',
        'path' => __DIR__ . '/database/clinical_audits.xml',
        'backup_enabled' => true,
        'backup_path' => __DIR__ . '/database/backups/',
        'auto_backup_frequency' => 'daily', // daily, weekly, monthly
    ],
    
    // Schema Validation
    'validation' => [
        'enabled' => true,
        'schema_path' => __DIR__ . '/schema/clinical_audit.xsd',
        'strict_mode' => false,
    ],
    
    // API Settings
    'api' => [
        'enable_cors' => true,
        'allowed_origins' => ['*'], // Change to specific domains in production
        'rate_limit' => [
            'enabled' => false,
            'max_requests' => 100,
            'time_window' => 3600, // 1 hour in seconds
        ],
    ],
    
    // Security
    'security' => [
        'require_authentication' => false, // Set to true in production
        'api_key_required' => false,
        'allowed_ips' => [], // Empty array allows all IPs
        'ssl_required' => false, // Set to true in production
    ],
    
    // Export Settings
    'export' => [
        'max_records' => 10000,
        'formats' => ['json', 'csv', 'xml'],
        'include_metadata' => true,
    ],
    
    // Form Validation
    'form' => [
        'required_fields' => [
            'patient-id',
            'data-collector',
            'collection-date',
            'age',
            'gender',
            'first-visit',
            'primary-diagnosis',
        ],
        'age_range' => [
            'min' => 0,
            'max' => 120,
        ],
    ],
    
    // Statistics & Analytics
    'analytics' => [
        'cache_enabled' => true,
        'cache_duration' => 300, // 5 minutes
        'default_page_size' => 20,
    ],
    
    // Logging
    'logging' => [
        'enabled' => true,
        'log_file' => __DIR__ . '/logs/app.log',
        'log_level' => 'INFO', // DEBUG, INFO, WARNING, ERROR
        'log_api_requests' => true,
    ],
    
    // Email Notifications (for future use)
    'notifications' => [
        'enabled' => false,
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_user' => '',
        'smtp_password' => '',
        'from_email' => 'noreply@hospital.org',
        'admin_email' => 'admin@hospital.org',
    ],
    
    // Application Settings
    'app' => [
        'name' => 'Clinical Audit Data Collection System',
        'version' => '1.0.0',
        'timezone' => 'Africa/Harare',
        'locale' => 'en_US',
        'debug_mode' => false, // Set to false in production
    ],
];
