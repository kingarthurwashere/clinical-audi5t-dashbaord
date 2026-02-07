<?php
/**
 * Export API Endpoint
 * Exports audit data in various formats
 */

require_once 'XmlOdm.php';

try {
    // Get format from query parameter
    $format = isset($_GET['format']) ? $_GET['format'] : 'json';
    
    // Initialize ODM
    $odm = new XmlOdm('database/clinical_audits.xml');
    
    // Export data
    $exportData = $odm->export($format);
    
    // Set appropriate headers
    switch ($format) {
        case 'json':
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="clinical_audit_export_' . date('Y-m-d') . '.json"');
            break;
            
        case 'csv':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="clinical_audit_export_' . date('Y-m-d') . '.csv"');
            break;
            
        case 'xml':
            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename="clinical_audit_export_' . date('Y-m-d') . '.xml"');
            break;
            
        default:
            throw new Exception("Unsupported format: $format");
    }
    
    echo $exportData;
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
