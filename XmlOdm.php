<?php
/**
 * XML Object-Document Mapper (ODM)
 * Provides CRUD operations for XML-based database storage
 */

require_once 'AuditRecord.php';

class XmlOdm {
    private $xmlFile;
    private $dom;
    private $rootElement;
    
    public function __construct($xmlFile) {
        $this->xmlFile = $xmlFile;
        $this->initializeDatabase();
    }
    
    /**
     * Initialize XML database file
     */
    private function initializeDatabase() {
        // Ensure database directory exists
        $dir = dirname($this->xmlFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Create XML file if it doesn't exist
        if (!file_exists($this->xmlFile)) {
            $this->createNewDatabase();
        }
        
        // Load existing database
        $this->loadDatabase();
    }
    
    /**
     * Create new XML database file
     */
    private function createNewDatabase() {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;
        
        // Create root element with metadata
        $root = $this->dom->createElement('clinical_audit_database');
        $root->setAttribute('version', '1.0');
        $root->setAttribute('created', date('Y-m-d H:i:s'));
        $this->dom->appendChild($root);
        
        // Create records container
        $records = $this->dom->createElement('records');
        $root->appendChild($records);
        
        // Save initial structure
        $this->dom->save($this->xmlFile);
    }
    
    /**
     * Load existing XML database
     */
    private function loadDatabase() {
        $this->dom = new DOMDocument();
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;
        
        if (!$this->dom->load($this->xmlFile)) {
            throw new Exception("Failed to load XML database");
        }
        
        $this->rootElement = $this->dom->documentElement;
    }
    
    /**
     * Save record to database
     * @param AuditRecord $record
     * @return string Record ID
     */
    public function save(AuditRecord $record) {
        // Generate unique ID if not set
        if (!$record->getId()) {
            $record->setId($this->generateId());
        }
        
        // Get records container
        $recordsContainer = $this->rootElement->getElementsByTagName('records')->item(0);
        
        // Check if record exists (update) or create new
        $existingRecord = $this->findRecordElementById($record->getId());
        
        if ($existingRecord) {
            // Update existing record
            $recordsContainer->replaceChild($record->toXml($this->dom), $existingRecord);
        } else {
            // Add new record
            $recordsContainer->appendChild($record->toXml($this->dom));
        }
        
        // Save to file
        $this->persist();
        
        return $record->getId();
    }
    
    /**
     * Find record by ID
     * @param string $id
     * @return AuditRecord|null
     */
    public function findById($id) {
        $element = $this->findRecordElementById($id);
        
        if (!$element) {
            return null;
        }
        
        $record = new AuditRecord();
        $record->fromXml($element);
        
        return $record;
    }
    
    /**
     * Find records by criteria
     * @param array $criteria Key-value pairs to match
     * @return array Array of AuditRecord objects
     */
    public function findBy(array $criteria) {
        $results = [];
        $xpath = new DOMXPath($this->dom);
        
        // Build XPath query
        $conditions = [];
        foreach ($criteria as $key => $value) {
            $conditions[] = "field[@name='$key' and text()='$value']";
        }
        $query = "//record[" . implode(' and ', $conditions) . "]";
        
        $nodes = $xpath->query($query);
        
        foreach ($nodes as $node) {
            $record = new AuditRecord();
            $record->fromXml($node);
            $results[] = $record;
        }
        
        return $results;
    }
    
    /**
     * Find all records
     * @return array Array of AuditRecord objects
     */
    public function findAll() {
        $results = [];
        $records = $this->dom->getElementsByTagName('record');
        
        foreach ($records as $recordElement) {
            $record = new AuditRecord();
            $record->fromXml($recordElement);
            $results[] = $record;
        }
        
        return $results;
    }
    
    /**
     * Delete record by ID
     * @param string $id
     * @return bool Success status
     */
    public function delete($id) {
        $element = $this->findRecordElementById($id);
        
        if (!$element) {
            return false;
        }
        
        $recordsContainer = $this->rootElement->getElementsByTagName('records')->item(0);
        $recordsContainer->removeChild($element);
        
        $this->persist();
        
        return true;
    }
    
    /**
     * Count total records
     * @return int
     */
    public function count() {
        return $this->dom->getElementsByTagName('record')->length;
    }
    
    /**
     * Get statistics from database
     * @return array
     */
    public function getStatistics() {
        $stats = [
            'total_records' => $this->count(),
            'diagnosis_breakdown' => [],
            'gender_breakdown' => ['male' => 0, 'female' => 0],
            'services_breakdown' => [],
            'avg_waiting_time' => 0
        ];
        
        $xpath = new DOMXPath($this->dom);
        
        // Count by diagnosis
        $diagnosisFields = $xpath->query("//field[@name='primary-diagnosis']");
        foreach ($diagnosisFields as $field) {
            $diagnosis = $field->nodeValue;
            if (!isset($stats['diagnosis_breakdown'][$diagnosis])) {
                $stats['diagnosis_breakdown'][$diagnosis] = 0;
            }
            $stats['diagnosis_breakdown'][$diagnosis]++;
        }
        
        // Count by gender
        $genderFields = $xpath->query("//field[@name='gender']");
        foreach ($genderFields as $field) {
            $gender = $field->nodeValue;
            if (isset($stats['gender_breakdown'][$gender])) {
                $stats['gender_breakdown'][$gender]++;
            }
        }
        
        // Count by services
        $servicesFields = $xpath->query("//field[@name='services']");
        foreach ($servicesFields as $field) {
            $service = $field->nodeValue;
            if (!isset($stats['services_breakdown'][$service])) {
                $stats['services_breakdown'][$service] = 0;
            }
            $stats['services_breakdown'][$service]++;
        }
        
        // Calculate average waiting time
        $waitingDays = $xpath->query("//field[@name='waiting-days']");
        $total = 0;
        $count = 0;
        foreach ($waitingDays as $field) {
            if (!empty($field->nodeValue)) {
                $total += (int)$field->nodeValue;
                $count++;
            }
        }
        if ($count > 0) {
            $stats['avg_waiting_time'] = round($total / $count, 1);
        }
        
        return $stats;
    }
    
    /**
     * Export database to different format
     * @param string $format 'json', 'csv', 'xml'
     * @return string Exported data
     */
    public function export($format = 'json') {
        $records = $this->findAll();
        
        switch ($format) {
            case 'json':
                return json_encode(array_map(function($r) { 
                    return $r->toArray(); 
                }, $records), JSON_PRETTY_PRINT);
                
            case 'csv':
                return $this->exportToCsv($records);
                
            case 'xml':
                return $this->dom->saveXML();
                
            default:
                throw new Exception("Unsupported export format: $format");
        }
    }
    
    /**
     * Export records to CSV format
     * @param array $records
     * @return string CSV data
     */
    private function exportToCsv(array $records) {
        if (empty($records)) {
            return '';
        }
        
        // Get all possible fields
        $fields = [];
        foreach ($records as $record) {
            $fields = array_merge($fields, array_keys($record->getData()));
        }
        $fields = array_unique($fields);
        
        // Create CSV
        $output = fopen('php://temp', 'r+');
        
        // Header row
        fputcsv($output, array_merge(['id', 'created_at', 'updated_at'], $fields));
        
        // Data rows
        foreach ($records as $record) {
            $row = [$record->getId()];
            $arr = $record->toArray();
            $row[] = $arr['created_at'];
            $row[] = $arr['updated_at'];
            
            foreach ($fields as $field) {
                $row[] = $record->get($field) ?? '';
            }
            
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    /**
     * Create backup of database
     * @return string Backup file path
     */
    public function backup() {
        $backupFile = $this->xmlFile . '.backup.' . date('Y-m-d_H-i-s') . '.xml';
        copy($this->xmlFile, $backupFile);
        return $backupFile;
    }
    
    /**
     * Find record element by ID
     * @param string $id
     * @return DOMElement|null
     */
    private function findRecordElementById($id) {
        $xpath = new DOMXPath($this->dom);
        $results = $xpath->query("//record[@id='$id']");
        
        return $results->length > 0 ? $results->item(0) : null;
    }
    
    /**
     * Generate unique ID
     * @return string
     */
    private function generateId() {
        return 'AUD' . date('Ymd') . '_' . uniqid();
    }
    
    /**
     * Validate XML against schema
     * @param string $schemaFile Path to XSD file
     * @return bool True if valid
     * @throws Exception If validation fails
     */
    public function validate($schemaFile = null) {
        if ($schemaFile === null) {
            $schemaFile = dirname(__FILE__) . '/schema/clinical_audit.xsd';
        }
        
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found: $schemaFile");
        }
        
        libxml_use_internal_errors(true);
        
        if (!$this->dom->schemaValidate($schemaFile)) {
            $errors = libxml_get_errors();
            $errorMessages = [];
            
            foreach ($errors as $error) {
                $errorMessages[] = sprintf(
                    "Line %d: %s",
                    $error->line,
                    trim($error->message)
                );
            }
            
            libxml_clear_errors();
            
            throw new Exception(
                "XML validation failed:\n" . implode("\n", $errorMessages)
            );
        }
        
        libxml_clear_errors();
        return true;
    }
    
    /**
     * Persist changes to file
     */
    private function persist() {
        if (!$this->dom->save($this->xmlFile)) {
            throw new Exception("Failed to save XML database");
        }
    }
    
    /**
     * Query records using XPath
     * @param string $xpath XPath query
     * @return array Array of AuditRecord objects
     */
    public function query($xpath) {
        $xpathObj = new DOMXPath($this->dom);
        $results = [];
        
        $nodes = $xpathObj->query($xpath);
        
        foreach ($nodes as $node) {
            if ($node->nodeName === 'record') {
                $record = new AuditRecord();
                $record->fromXml($node);
                $results[] = $record;
            }
        }
        
        return $results;
    }
}
