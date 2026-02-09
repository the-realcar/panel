<?php
/**
 * Validator Class
 * Form Validation
 * Panel Pracowniczy Firma KOT
 */

class Validator {
    private $errors = [];
    private $data = [];
    
    /**
     * Constructor
     * 
     * @param array $data Data to validate
     */
    public function __construct(array $data = []) {
        $this->data = $data;
    }
    
    /**
     * Set data to validate
     * 
     * @param array $data
     */
    public function setData(array $data) {
        $this->data = $data;
        $this->errors = [];
    }
    
    /**
     * Validate required field
     * 
     * @param string $field
     * @param string $message
     * @return $this
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?? "Pole {$field} jest wymagane.";
        }
        return $this;
    }
    
    /**
     * Validate email
     * 
     * @param string $field
     * @param string $message
     * @return $this
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Pole {$field} musi być poprawnym adresem email.";
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     * 
     * @param string $field
     * @param int $length
     * @param string $message
     * @return $this
     */
    public function minLength($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?? "Pole {$field} musi mieć co najmniej {$length} znaków.";
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     * 
     * @param string $field
     * @param int $length
     * @param string $message
     * @return $this
     */
    public function maxLength($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?? "Pole {$field} nie może mieć więcej niż {$length} znaków.";
        }
        return $this;
    }
    
    /**
     * Validate numeric
     * 
     * @param string $field
     * @param string $message
     * @return $this
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? "Pole {$field} musi być liczbą.";
        }
        return $this;
    }
    
    /**
     * Validate integer
     * 
     * @param string $field
     * @param string $message
     * @return $this
     */
    public function integer($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field] = $message ?? "Pole {$field} musi być liczbą całkowitą.";
        }
        return $this;
    }
    
    /**
     * Validate minimum value
     * 
     * @param string $field
     * @param mixed $min
     * @param string $message
     * @return $this
     */
    public function min($field, $min, $message = null) {
        if (isset($this->data[$field]) && $this->data[$field] < $min) {
            $this->errors[$field] = $message ?? "Pole {$field} musi być większe lub równe {$min}.";
        }
        return $this;
    }
    
    /**
     * Validate maximum value
     * 
     * @param string $field
     * @param mixed $max
     * @param string $message
     * @return $this
     */
    public function max($field, $max, $message = null) {
        if (isset($this->data[$field]) && $this->data[$field] > $max) {
            $this->errors[$field] = $message ?? "Pole {$field} musi być mniejsze lub równe {$max}.";
        }
        return $this;
    }
    
    /**
     * Validate field matches another field
     * 
     * @param string $field
     * @param string $match_field
     * @param string $message
     * @return $this
     */
    public function matches($field, $match_field, $message = null) {
        if (isset($this->data[$field]) && isset($this->data[$match_field]) && 
            $this->data[$field] !== $this->data[$match_field]) {
            $this->errors[$field] = $message ?? "Pole {$field} musi być takie samo jak {$match_field}.";
        }
        return $this;
    }
    
    /**
     * Validate using regex pattern
     * 
     * @param string $field
     * @param string $pattern
     * @param string $message
     * @return $this
     */
    public function pattern($field, $pattern, $message = null) {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field] = $message ?? "Pole {$field} ma nieprawidłowy format.";
        }
        return $this;
    }
    
    /**
     * Validate date format
     * 
     * @param string $field
     * @param string $format
     * @param string $message
     * @return $this
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        if (isset($this->data[$field])) {
            $d = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$d || $d->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message ?? "Pole {$field} musi być poprawną datą w formacie {$format}.";
            }
        }
        return $this;
    }
    
    /**
     * Validate time format
     * 
     * @param string $field
     * @param string $format
     * @param string $message
     * @return $this
     */
    public function time($field, $format = 'H:i', $message = null) {
        if (isset($this->data[$field])) {
            $t = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$t || $t->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message ?? "Pole {$field} musi być poprawnym czasem w formacie {$format}.";
            }
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     * 
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     * 
     * @return bool
     */
    public function fails() {
        return !empty($this->errors);
    }
    
    /**
     * Get all errors
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get first error
     * 
     * @return string|null
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    /**
     * Get error for specific field
     * 
     * @param string $field
     * @return string|null
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Add custom error
     * 
     * @param string $field
     * @param string $message
     */
    public function addError($field, $message) {
        $this->errors[$field] = $message;
    }
}
