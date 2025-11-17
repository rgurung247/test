<?php
// Simple File Management System with Encryption
class FileManager {
    private $encryptionKey = 'your-secret-key-here'; // Change this!
    
    public function encryptData($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    public function decryptData($data) {
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
    }
    
    public function createFile($filename, $content, $encrypt = false) {
        if ($encrypt) {
            $content = $this->encryptData($content);
            $filename = $filename . '.enc';
        }
        
        if (file_put_contents($filename, $content) !== false) {
            return "File '$filename' created successfully!";
        }
        return "Error creating file '$filename'!";
    }
    
    public function readFile($filename, $decrypt = false) {
        if (!file_exists($filename)) {
            return "File '$filename' not found!";
        }
        
        $content = file_get_contents($filename);
        
        if ($decrypt) {
            try {
                $content = $this->decryptData($content);
            } catch (Exception $e) {
                return "Error decrypting file: " . $e->getMessage();
            }
        }
        
        return $content;
    }
    
    public function listFiles($pattern = '*') {
        $files = glob($pattern);
        return array_map('basename', $files);
    }
    
    public function fileInfo($filename) {
        if (!file_exists($filename)) {
            return "File '$filename' not found!";
        }
        
        $info = [
            'name' => $filename,
            'size' => filesize($filename) . ' bytes',
            'modified' => date('Y-m-d H:i:s', filemtime($filename)),
            'type' => filetype($filename),
            'is_encrypted' => substr($filename, -4) === '.enc'
        ];
        
        return $info;
    }
}

// Usage example
$fileManager = new FileManager();

if ($_POST) {
    if (isset($_POST['create_file'])) {
        $result = $fileManager->createFile(
            $_POST['filename'], 
            $_POST['content'], 
            isset($_POST['encrypt'])
        );
    } elseif (isset($_POST['read_file'])) {
        $content = $fileManager->readFile(
            $_POST['read_filename'], 
            isset($_POST['decrypt'])
        );
        $result = "File content: " . htmlspecialchars($content);
    }
}

$files = $fileManager->listFiles();
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP File Manager</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 100px; }
        button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .file-list { background: #f8f9fa; padding: 15px; border-radius: 4px; }
        .file-item { padding: 5px 0; border-bottom: 1px solid #eee; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>PHP File Management System</h1>
    
    <?php if (isset($result)): ?>
        <div class="message <?php echo strpos($result, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?php echo $result; ?>
        </div>
    <?php endif; ?>
    
    <div class="section">
        <h2>Create New File</h2>
        <form method="POST">
            <div class="form-group">
                <label>Filename:</label>
                <input type="text" name="filename" placeholder="example.txt" required>
            </div>
            <div class="form-group">
                <label>Content:</label>
                <textarea name="content" placeholder="Enter file content here..." required></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="encrypt"> Encrypt file content
                </label>
            </div>
            <button type="submit" name="create_file">Create File</button>
        </form>
    </div>
    
    <div class="section">
        <h2>Read File</h2>
        <form method="POST">
            <div class="form-group">
                <label>Filename:</label>
                <input type="text" name="read_filename" placeholder="example.txt or example.txt.enc" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="decrypt"> Decrypt file content
                </label>
            </div>
            <button type="submit" name="read_file">Read File</button>
        </form>
    </div>
    
    <div class="section">
        <h2>Available Files</h2>
        <div class="file-list">
            <?php if (empty($files)): ?>
                <p>No files found.</p>
            <?php else: ?>
                <?php foreach ($files as $file): ?>
                    <div class="file-item">
                        <strong><?php echo $file; ?></strong>
                        <?php
                        $info = $fileManager->fileInfo($file);
                        if (is_array($info)) {
                            echo " - " . $info['size'] . " - " . $info['modified'];
                            if ($info['is_encrypted']) {
                                echo " <span style='color: red;'>(Encrypted)</span>";
                            }
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>