<?php
    header('Content-Type: application/json; charset=UTF-8');
    $baseDir = __DIR__; // Root directory (server's current directory)
    $hiddenFiles = ['index.php', 'file_explorer.php']; // Files to hide

function sanitizePath($path) {
    return str_replace('..', '', $path); // Basic security to prevent directory traversal
}

if (isset($_GET['action']) || isset($_POST['action'])) {
    $action = $_GET['action'] ?? $_POST['action'];

    if ($action === 'list') {
        $path = sanitizePath($_GET['path'] ?? '');
        $fullPath = $baseDir . '/' . $path;
        if (!is_dir($fullPath)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid directory']);
            exit;
        }
        $files = scandir($fullPath);
        $result = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || in_array($file, $GLOBALS['hiddenFiles'])) continue;
            $isDir = is_dir($fullPath . '/' . $file);
            $result[] = ['name' => $file, 'isDir' => $isDir];
        }
        echo json_encode($result);
    } elseif ($action === 'download') {
        $path = sanitizePath($_GET['path']);
        $filePath = $baseDir . '/' . $path;
        if (in_array(basename($filePath), $GLOBALS['hiddenFiles'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Access to this file is restricted']);
            exit;
        }
        if (file_exists($filePath) && !is_dir($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            readfile($filePath);
            exit;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'File not found']);
        }
    } elseif ($action === 'createFolder') {
        $path = sanitizePath($_POST['path'] ?? '');
        $name = sanitizePath($_POST['name'] ?? '');
        $fullPath = $baseDir . '/' . $path . '/' . $name;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
            echo 'Folder created';
        } else {
            echo 'Folder already exists';
        }
    } elseif ($action === 'upload') {
        $path = sanitizePath($_POST['path'] ?? '');
        $fullPath = $baseDir . '/' . $path;
        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            if (in_array($file['name'], $GLOBALS['hiddenFiles'])) {
                http_response_code(403);
                echo 'Cannot upload restricted file';
                exit;
            }
            $destination = $fullPath . '/' . $file['name'];
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                echo 'File uploaded';
            } else {
                http_response_code(500);
                echo 'Upload failed';
            }
        }
    } elseif ($action === 'cut' || $action === 'copy') {
        $source = sanitizePath($_POST['source']);
        $dest = sanitizePath($_POST['dest']);
        $sourcePath = $baseDir . '/' . $source;
        $destPath = $baseDir . '/' . $dest . '/' . basename($source);
        if (in_array(basename($sourcePath), $GLOBALS['hiddenFiles'])) {
            http_response_code(403);
            echo 'Cannot modify restricted file';
            exit;
        }
        if (file_exists($sourcePath)) {
            if ($action === 'cut') {
                if (rename($sourcePath, $destPath)) {
                    echo 'File moved';
                } else {
                    http_response_code(500);
                    echo 'Move failed';
                }
            } else {
                if (is_dir($sourcePath)) {
                    function copyDir($src, $dst) {
                        mkdir($dst, 0777, true);
                        $dir = opendir($src);
                        while (($file = readdir($dir)) !== false) {
                            if ($file === '.' || $file === '..' || in_array($file, $GLOBALS['hiddenFiles'])) continue;
                            $srcPath = "$src/$file";
                            $dstPath = "$dst/$file";
                            if (is_dir($srcPath)) copyDir($srcPath, $dstPath);
                            else copy($srcPath, $dstPath);
                        }
                        closedir($dir);
                    }
                    copyDir($sourcePath, $destPath);
                } else {
                    copy($sourcePath, $destPath);
                }
                echo 'File copied';
            }
        } else {
            http_response_code(404);
            echo 'Source not found';
        }
    } elseif ($action === 'delete') {
        $path = sanitizePath($_POST['path']);
        $fullPath = $baseDir . '/' . $path;
        if (in_array(basename($fullPath), $GLOBALS['hiddenFiles'])) {
            http_response_code(403);
            echo 'Cannot delete restricted file';
            exit;
        }
        if (file_exists($fullPath)) {
            if (is_dir($fullPath)) {
                function deleteDir($dir) {
                    $files = array_diff(scandir($dir), array_merge(['.', '..'], $GLOBALS['hiddenFiles']));
                    foreach ($files as $file) {
                        $path = "$dir/$file";
                        is_dir($path) ? deleteDir($path) : unlink($path);
                    }
                    rmdir($dir);
                }
                deleteDir($fullPath);
            } else {
                unlink($fullPath);
            }
            echo 'File deleted';
        } else {
            http_response_code(404);
            echo 'File not found';
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'No action specified']);
?>