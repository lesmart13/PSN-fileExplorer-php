<?php
header('Content-Type: text/plain');
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$path = $_GET['path'] ?? $_POST['path'] ?? '';
$baseDir = './'; // Base directory (adjust as needed, e.g., '/path/to/repo/')

// Normalize path to prevent double slashes
$fullPath = rtrim($baseDir, '/') . '/' . ltrim($path, '/');
if (!is_dir($baseDir)) {
    die("Base directory does not exist or is not accessible");
}

switch ($action) {
    case 'list':
        if (!is_dir($fullPath)) die("Invalid directory: $fullPath");
        $files = scandir($fullPath);
        $result = [];
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $result[] = [
                    'name' => $file,
                    'isDir' => is_dir($fullPath . '/' . $file)
                ];
            }
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    case 'createFolder':
        $name = $_POST['name'] ?? '';
        if (!$name) die('No folder name provided');
        $newFolderPath = $fullPath . '/' . $name;
        if (file_exists($newFolderPath)) {
            die("Folder already exists: $name");
        }
        if (mkdir($newFolderPath)) {
            echo "Folder created successfully: $name";
        } else {
            echo "Failed to create folder: Permission denied or invalid name";
        }
        break;

    case 'upload':
        if (!isset($_FILES['file'])) die('No file uploaded');
        $file = $_FILES['file'];
        $uploadPath = $fullPath . '/' . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            echo "File uploaded successfully: " . basename($file['name']);
        } else {
            echo "Failed to upload file: Permission denied or upload error";
        }
        break;

    case 'download':
        $filePath = $fullPath;
        if (!file_exists($filePath) || is_dir($filePath)) {
            die("File not found or is a directory: $filePath");
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;

    case 'cut':
    case 'copy':
        $source = $_POST['source'] ?? '';
        $dest = $_POST['dest'] ?? '';
        if (!$source || !$dest) die('Source or destination path missing');
        $sourcePath = rtrim($baseDir, '/') . '/' . ltrim($source, '/');
        $destPath = rtrim($baseDir, '/') . '/' . ltrim($dest, '/') . '/' . basename($source);
        
        if (!file_exists($sourcePath)) die("Source not found: $source");
        if (file_exists($destPath)) die("Destination already exists: " . basename($source));
        
        if ($action === 'cut') {
            if (rename($sourcePath, $destPath)) {
                echo "File moved successfully: " . basename($source);
            } else {
                echo "Failed to move file: Permission denied or error";
            }
        } else { // copy
            if (is_dir($sourcePath)) {
                function copyDir($src, $dst) {
                    if (!is_dir($dst)) mkdir($dst);
                    $files = scandir($src);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..') {
                            $srcPath = "$src/$file";
                            $dstPath = "$dst/$file";
                            if (is_dir($srcPath)) {
                                copyDir($srcPath, $dstPath);
                            } else {
                                copy($srcPath, $dstPath);
                            }
                        }
                    }
                }
                copyDir($sourcePath, $destPath);
                echo "Folder copied successfully: " . basename($source);
            } else {
                if (copy($sourcePath, $destPath)) {
                    echo "File copied successfully: " . basename($source);
                } else {
                    echo "Failed to copy file: Permission denied or error";
                }
            }
        }
        break;

    case 'paste':
        // Handled by cut/copy above; this case is redundant but kept for clarity
        echo "Paste action not directly handled; use cut or copy";
        break;

    case 'delete':
        $deletePath = $fullPath;
        if (!file_exists($deletePath)) die("Path not found: $deletePath");
        if (is_dir($deletePath)) {
            function deleteDir($dir) {
                $files = array_diff(scandir($dir), ['.', '..']);
                foreach ($files as $file) {
                    $path = "$dir/$file";
                    is_dir($path) ? deleteDir($path) : unlink($path);
                }
                return rmdir($dir);
            }
            if (deleteDir($deletePath)) {
                echo "Folder deleted successfully: " . basename($deletePath);
            } else {
                echo "Failed to delete folder: Permission denied or error";
            }
        } else {
            if (unlink($deletePath)) {
                echo "File deleted successfully: " . basename($deletePath);
            } else {
                echo "Failed to delete file: Permission denied or error";
            }
        }
        break;

    case 'search':
        $query = $_GET['query'] ?? '';
        if (!$query) die('No search query provided');
        $result = [];
        function searchDir($dir, $query, &$result) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = "$dir/$file";
                    if (stripos($file, $query) !== false) {
                        $result[] = [
                            'name' => $file,
                            'isDir' => is_dir($filePath)
                        ];
                    }
                    if (is_dir($filePath)) {
                        searchDir($filePath, $query, $result);
                    }
                }
            }
        }
        searchDir($fullPath, $query, $result);
        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    default:
        echo "Invalid action: $action";
}
?>