<?php
function pre($s, $e=0) { printf('<pre>%s</pre>', print_r($s,1)); if ($e) exit; }

function return_bytes($val = null) {
    if (!$val) return;
    if (is_numeric($val)) return $val;
    $val = trim($val);
    $uni = strtoupper($val[strlen($val)-1]);
    if ($uni == 'K') return $val = $val * 1024;
    if ($uni == 'M') return $val = $val * 1048576;
    if ($uni == 'G') return $val = $val * 1073741824;
}

function return_unit($size, $unit = 'MB') {
    // GB?
    $a = round($size / 1024, 1);
    if ($a < 1024)  return $a .' KB';
    if ($a >= 1024) return round($a / 1024, 1) .' MB';
    return $a;
}

define('UPLOAD_MAX_SIZE', ini_get('upload_max_filesize'));
define('UPLOAD_MAX_SIZE_BYTES', return_bytes(UPLOAD_MAX_SIZE));

class Upload
{
    private $mimeTypes = array('txt' => 'text/plain', 'pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'xls' => 'application/excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    private $dir = 'uploads/';
    private $errors = array();
    
    public function __construct($file, $dir = null) {
        // Set file
        $this->file = $file;
        // Check size
        if ($this->file['error']) {
            $this->errors[] = 'No file selected!';
            return;
        }
        // Check size
        if ($this->file['size'] > UPLOAD_MAX_SIZE_BYTES) {
            $this->errors[] = sprintf('File too big! Maximum value: %d bytes (%s).', 
                                        UPLOAD_MAX_SIZE_BYTES, UPLOAD_MAX_SIZE);
            return;
        }
        // Check mime
        if (!in_array($this->file['type'], $this->mimeTypes)) {
            $this->errors[] = sprintf('File type not allowed! Allowed types: [%s].', 
                                        join(', ', array_keys($this->mimeTypes)));
            return;
        }
        
        // Get path info
        $pathinfo = pathinfo($this->file['name']);
        
        // Set file bytes to unit
        $this->file['byte'] = return_unit($this->file['size']);
        
        // Set file name
        if ($pathinfo['filename'] == '') {
            $this->errors[] = sprintf('File name not specified!');
            return;
        }
        $this->file['name'] = $pathinfo['filename'];
        
        // Set file path
        if ($dir) $this->dir = $dir;
        // Create file path
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
            chmod($this->dir, 0777);
        }
        // This path used for upload
        $this->file['path'] = $this->dir . 
                                sprintf('%s-%s.%s', 
                                           $this->slug($pathinfo['filename']), $this->uniqid(), $pathinfo['extension']);
    }
    
    public function load() {
        $srcfile = $this->file['tmp_name'];
        $dstfile = $this->file['path'];
        // Try move
        if (!@move_uploaded_file($srcfile, $dstfile)) {
            // Try copy
            if (!@copy($srcfile, $dstfile)) {
                $this->errors[] = sprintf('System error! Upload failed, file: "%s"', 
                                            $this->file['path']);
            }
        }
        // Remove un-needed fields
        unset($this->file['tmp_name'], $this->file['error']);
        // GC run if file not moved
        @unlink($srcfile);
    }
    
    public function slug($input) {
        return preg_replace(
            array('~[^a-zA-Z0-9]~', '~\s+~'),
            array(' ', '-'), 
            strtolower($input)
        );
    }
    
    public function uniqid() {
        return hash('crc32b', uniqid(rand() . microtime(), true));
    }
    
    public function isError() {
        return !empty($this->errors);
    }
    
    public function getErrors($format = false) {
        if (!$format) {
            return $this->errors;
        }
        $str = '<ul>';
        foreach ($this->errors as $error) {
            $str .= '<li>'. $error .'</li>';
        }
        $str .= '</ul>';
        return $str;
    }
}
