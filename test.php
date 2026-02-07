<?php
/**
 * Test Script for Clinical Audit System
 * Run this to verify the system is working correctly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'XmlOdm.php';

echo "=== Clinical Audit System Test ===\n\n";

try {
    // Test 1: Initialize ODM
    echo "Test 1: Initializing XML ODM...\n";
    $odm = new XmlOdm('database/clinical_audits_test.xml');
    echo "✓ ODM initialized successfully\n\n";
    
    // Test 2: Create sample records
    echo "Test 2: Creating sample records...\n";
    
    $sampleData = [
        [
            'patient-id' => 'RTC10001',
            'data-collector' => 'JD',
            'collection-date' => '2024-02-01',
            'age' => '55',
            'gender' => 'female',
            'first-visit' => '2024-01-15',
            'primary-diagnosis' => 'breast-cancer',
            'cancer-stage' => 'stage-2',
            'services' => 'radiotherapy-only',
            'rt-decision-date' => '2024-01-20',
            'rt-start-date' => '2024-02-10',
            'waiting-days' => '21',
            'waiting-weeks' => '3.0',
        ],
        [
            'patient-id' => 'RTC10002',
            'data-collector' => 'SM',
            'collection-date' => '2024-02-02',
            'age' => '62',
            'gender' => 'male',
            'first-visit' => '2024-01-18',
            'primary-diagnosis' => 'prostate-cancer',
            'cancer-stage' => 'stage-3',
            'services' => 'radiotherapy-chemotherapy',
            'rt-decision-date' => '2024-01-25',
            'rt-start-date' => '2024-02-20',
            'waiting-days' => '26',
            'waiting-weeks' => '3.7',
        ],
        [
            'patient-id' => 'RTC10003',
            'data-collector' => 'TM',
            'collection-date' => '2024-02-03',
            'age' => '48',
            'gender' => 'female',
            'first-visit' => '2024-01-22',
            'primary-diagnosis' => 'cervical-cancer',
            'cancer-stage' => 'stage-2',
            'services' => 'radiotherapy-only',
            'rt-decision-date' => '2024-01-28',
            'rt-start-date' => '2024-02-25',
            'waiting-days' => '28',
            'waiting-weeks' => '4.0',
        ],
    ];
    
    $recordIds = [];
    foreach ($sampleData as $data) {
        $record = new AuditRecord();
        $record->setData($data);
        $id = $odm->save($record);
        $recordIds[] = $id;
        echo "  ✓ Created record: {$data['patient-id']} (ID: $id)\n";
    }
    echo "\n";
    
    // Test 3: Retrieve records
    echo "Test 3: Retrieving records...\n";
    $allRecords = $odm->findAll();
    echo "  ✓ Found " . count($allRecords) . " records\n\n";
    
    // Test 4: Find by criteria
    echo "Test 4: Finding records by criteria...\n";
    $femalePatients = $odm->findBy(['gender' => 'female']);
    echo "  ✓ Found " . count($femalePatients) . " female patients\n";
    
    $breastCancer = $odm->findBy(['primary-diagnosis' => 'breast-cancer']);
    echo "  ✓ Found " . count($breastCancer) . " breast cancer patients\n\n";
    
    // Test 5: Statistics
    echo "Test 5: Generating statistics...\n";
    $stats = $odm->getStatistics();
    echo "  ✓ Total records: " . $stats['total_records'] . "\n";
    echo "  ✓ Male patients: " . $stats['gender_breakdown']['male'] . "\n";
    echo "  ✓ Female patients: " . $stats['gender_breakdown']['female'] . "\n";
    echo "  ✓ Average waiting time: " . $stats['avg_waiting_time'] . " days\n\n";
    
    echo "  Diagnosis breakdown:\n";
    foreach ($stats['diagnosis_breakdown'] as $diagnosis => $count) {
        echo "    - $diagnosis: $count\n";
    }
    echo "\n";
    
    // Test 6: Export functionality
    echo "Test 6: Testing export functionality...\n";
    $jsonExport = $odm->export('json');
    echo "  ✓ JSON export: " . strlen($jsonExport) . " bytes\n";
    
    $csvExport = $odm->export('csv');
    echo "  ✓ CSV export: " . strlen($csvExport) . " bytes\n\n";
    
    // Test 7: Update record
    echo "Test 7: Updating a record...\n";
    $recordToUpdate = $odm->findById($recordIds[0]);
    $recordToUpdate->set('age', '56');
    $odm->save($recordToUpdate);
    echo "  ✓ Updated record {$recordIds[0]}\n\n";
    
    // Test 8: XPath query
    echo "Test 8: Testing XPath queries...\n";
    $results = $odm->query("//record[field[@name='gender' and text()='female']]");
    echo "  ✓ XPath query found " . count($results) . " female patients\n\n";
    
    // Test 9: Backup
    echo "Test 9: Creating backup...\n";
    $backupFile = $odm->backup();
    echo "  ✓ Backup created: $backupFile\n\n";
    
    // Test 10: Validation (if schema exists)
    if (file_exists('schema/clinical_audit.xsd')) {
        echo "Test 10: Validating XML against schema...\n";
        try {
            $odm->validate('schema/clinical_audit.xsd');
            echo "  ✓ XML is valid according to schema\n\n";
        } catch (Exception $e) {
            echo "  ✗ Validation failed: " . $e->getMessage() . "\n\n";
        }
    } else {
        echo "Test 10: Skipped (schema file not found)\n\n";
    }
    
    echo "=== All Tests Completed Successfully ===\n\n";
    
    // Display sample record
    echo "Sample Record (JSON format):\n";
    echo json_encode($allRecords[0]->toArray(), JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
