<?php
// test_flow.php
// Script to verify data flow from save-audit.php to statistics.php

echo "Starting verification...\n";

// 1. Emulate Form Submission
$url = 'http://localhost:8000/save-audit.php'; // We will run the server
$data = [
    'patient-id' => 'TEST-' . uniqid(),
    'data-collector' => 'Tester',
    'collection-date' => date('Y-m-d'),
    'age' => 45,
    'gender' => 'female',
    'first-visit' => date('Y-m-d'),
    'primary-diagnosis' => 'breast-cancer',
    'services' => 'consultation-only',
    'waiting-days' => 10
];

// Clean up any existing test DB for a clean state if possible, or just append
// Ideally we'd use a separate test DB file, but for this quick check, appending is fine.
// Actually, let's just interact with the classes directly to avoid needing a web server for this specific PHP script test
// This is faster and less error prone for a backend logic check.

require_once 'XmlOdm.php';

echo "Initializing Database...\n";
// Use a test database file
$testDbFile = 'database/test_clinical_audits.xml';
if (file_exists($testDbFile)) {
    unlink($testDbFile);
}

$odm = new XmlOdm($testDbFile);
echo "Database initialized at $testDbFile\n";

// Create Record
$record = new AuditRecord();
$record->setData($data);
$id = $odm->save($record);
echo "Saved record with ID: $id\n";

// Verify it exists
$retrieved = $odm->findById($id);
if ($retrieved && $retrieved->get('patient-id') === $data['patient-id']) {
    echo "SUCCESS: Record retrieved successfully.\n";
} else {
    echo "FAILURE: Record not retrieved or data mismatch.\n";
    exit(1);
}

// Verify Statistics
$stats = $odm->getStatistics();
echo "Statistics retrieved:\n";
print_r($stats);

if ($stats['total_records'] === 1 && $stats['gender_breakdown']['female'] === 1) {
    echo "SUCCESS: Statistics are correct.\n";
} else {
    echo "FAILURE: Statistics mismatch.\n";
    exit(1);
}

echo "Verification Complete. Cleaning up...\n";
unlink($testDbFile);
echo "Done.\n";
