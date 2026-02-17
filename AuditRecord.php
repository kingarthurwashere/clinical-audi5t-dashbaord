<?php
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
        return htmlspecialchars($value ?? '', ENT_XML1, 'UTF-8');
    }
}
