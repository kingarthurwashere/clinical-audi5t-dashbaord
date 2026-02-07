<?php
/**
 * Clinical Audit Data Collection - XML Database Handler
 * Uses XML as database with ODM pattern
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'XmlOdm.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON input
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            throw new Exception('Invalid JSON data');
        }
        
        // Validate required fields
        $required = ['patient-id', 'data-collector', 'collection-date', 'age', 'gender', 'first-visit', 'primary-diagnosis'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Required field missing: $field");
            }
        }
        
        // Initialize ODM
        $odm = new XmlOdm('database/clinical_audits.xml');
        
        // Create audit record
        $auditRecord = new AuditRecord();
        $auditRecord->setData($data);
        
        // Save to XML database
        $recordId = $odm->save($auditRecord);
        
        echo json_encode([
            'success' => true,
            'id' => $recordId,
            'message' => 'Audit record saved successfully'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Initialize ODM
        $odm = new XmlOdm('database/clinical_audits.xml');
        
        // Get query parameters
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $patientId = isset($_GET['patient-id']) ? $_GET['patient-id'] : null;
        
        if ($id) {
            // Retrieve single record by ID
            $record = $odm->findById($id);
            echo json_encode([
                'success' => true,
                'data' => $record ? $record->toArray() : null
            ]);
        } elseif ($patientId) {
            // Search by patient ID
            $records = $odm->findBy(['patient-id' => $patientId]);
            echo json_encode([
                'success' => true,
                'data' => array_map(function($r) { return $r->toArray(); }, $records),
                'count' => count($records)
            ]);
        } else {
            // Get all records
            $records = $odm->findAll();
            echo json_encode([
                'success' => true,
                'data' => array_map(function($r) { return $r->toArray(); }, $records),
                'count' => count($records)
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

/**
 * Audit Record Entity Class
 */
class AuditRecord {
    private $id;
    private $data = [];
    private $createdAt;
    private $updatedAt;
    
    public function __construct() {
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setData(array $data) {
        $this->data = $data;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
    
    public function set($key, $value) {
        $this->data[$key] = $value;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function toArray() {
        return [
            'id' => $this->id,
            'data' => $this->data,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
    
    public function toXml(DOMDocument $dom) {
        $recordElement = $dom->createElement('record');
        $recordElement->setAttribute('id', $this->id);
        $recordElement->setAttribute('created_at', $this->createdAt);
        $recordElement->setAttribute('updated_at', $this->updatedAt);
        
        // Add all data fields
        foreach ($this->data as $key => $value) {
            $fieldElement = $dom->createElement('field');
            $fieldElement->setAttribute('name', $key);
            $fieldElement->appendChild($dom->createTextNode($this->xmlSafe($value)));
            $recordElement->appendChild($fieldElement);
        }
        
        return $recordElement;
    }
    
    public function fromXml(DOMElement $element) {
        $this->id = $element->getAttribute('id');
        $this->createdAt = $element->getAttribute('created_at');
        $this->updatedAt = $element->getAttribute('updated_at');
        
        $fields = $element->getElementsByTagName('field');
        foreach ($fields as $field) {
            $name = $field->getAttribute('name');
            $value = $field->nodeValue;
            $this->data[$name] = $value;
        }
    }
    
    private function xmlSafe($value) {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }
}
