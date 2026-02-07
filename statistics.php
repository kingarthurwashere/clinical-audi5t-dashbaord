<?php
/**
 * Statistics API Endpoint
 * Provides aggregated data for dashboard
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'XmlOdm.php';

try {
    // Initialize ODM
    $odm = new XmlOdm('database/clinical_audits.xml');
    
    // Get statistics
    $statistics = $odm->getStatistics();
    
    // Get all records
    $records = $odm->findAll();
    
    // Convert records to array
    $recordsArray = array_map(function($r) {
        return $r->toArray();
    }, $records);
    
    echo json_encode([
        'success' => true,
        'statistics' => $statistics,
        'records' => $recordsArray,
        'generated_at' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
