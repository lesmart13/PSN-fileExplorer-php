<?php 
    header('Content-Type: text/html; charset=UTF-8');
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Explorer</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
            overflow-x: hidden;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .header {
            position: sticky;
            top: 0;
            background: transparent; /* Removed white background */
            padding: 1rem;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            color: #1976d2;
        }
        .breadcrumbs span {
            cursor: pointer;
            transition: color 0.2s;
        }
        .breadcrumbs span:hover {
            color: #0d47a1;
        }
        .search-bar {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 0.5rem 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            flex: 1;
            max-width: 300px;
            min-width: 150px;
        }
        .search-bar input {
            border: none;
            outline: none;
            font-size: 1rem;
            width: 100%;
            background: transparent;
        }
        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }
        .file-item {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .file-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .file-item.selected {
            border: 2px solid #1976d2;
        }
        .file-icon {
            font-size: 1.5rem;
        }
        .folder .file-icon { color: #1976d2; }
        .file .file-icon { color: #757575; }
        .fab-container {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            z-index: 20;
        }
        .fab {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #1976d2;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s, background 0.2s;
        }
        .fab:hover {
            transform: scale(1.1);
            background: #0d47a1;
        }
        .fab.secondary {
            background: #fff;
            color: #1976d2;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .fab.secondary:hover {
            background: #f5f5f5;
        }
        /* Enhanced Responsiveness */
        @media (max-width: 1024px) {
            .file-list {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }
        }
        @media (max-width: 768px) {
            .file-list {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                padding: 0.75rem;
            }
            .header {
                padding: 0.75rem;
            }
            .search-bar {
                max-width: 250px;
            }
            .fab {
                width: 40px;
                height: 40px;
            }
        }
        @media (max-width: 480px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                padding: 0.5rem;
            }
            .search-bar {
                max-width: 100%;
                width: 100%;
                margin-top: 0.5rem;
            }
            .file-list {
                grid-template-columns: 1fr;
                padding: 0.5rem;
            }
            .file-item {
                padding: 0.75rem;
            }
            .fab-container {
                bottom: 0.5rem;
                right: 0.5rem;
                gap: 0.5rem;
            }
            .fab {
                width: 36px;
                height: 36px;
            }
            .file-icon {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="breadcrumbs" id="breadcrumbs">
                <span onclick="fetchFiles('')">/</span>
            </div>
            <div class="search-bar">
                <span class="material-icons">search</span>
                <input type="text" id="searchInput" placeholder="Search files..." oninput="searchFiles()">
            </div>
        </div>
        <div id="fileList" class="file-list"></div>
    </div>
    <div class="fab-container">
        <button class="fab" onclick="createFolder()" title="New Folder">
            <span class="material-icons">create_new_folder</span>
        </button>
        <label class="fab secondary" title="Upload File">
            <span class="material-icons">upload_file</span>
            <input type="file" id="fileUpload" onchange="uploadFile()" style="display: none;">
        </label>
        <button class="fab secondary" onclick="cutFile()" title="Cut">
            <span class="material-icons">content_cut</span>
        </button>
        <button class="fab secondary" onclick="copyFile()" title="Copy">
            <span class="material-icons">content_copy</span>
        </button>
        <button class="fab secondary" onclick="pasteFile()" title="Paste">
            <span class="material-icons">content_paste</span>
        </button>
        <button class="fab secondary" onclick="deleteFile()" title="Delete">
            <span class="material-icons">delete</span>
        </button>
        <button class="fab secondary" onclick="shareFile()" title="Share">
            <span class="material-icons">share</span>
        </button>
    </div>

    <script>
        let currentPath = '';
        let clipboard = { type: null, path: null };
        let allFiles = [];

        function fetchFiles(path = '') {
            fetch('file_explorer.php?action=list&path=' + encodeURIComponent(path))
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    currentPath = path;
                    allFiles = data;
                    updateBreadcrumbs();
                    renderFileList(data);
                })
                .catch(error => alert('Error fetching files: ' + error));
        }

        function renderFileList(files) {
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = '';
            files.forEach(item => {
                const div = document.createElement('div');
                div.className = `file-item ${item.isDir ? 'folder' : 'file'}`;
                div.dataset.path = currentPath ? `${currentPath}/${item.name}` : item.name;
                div.innerHTML = `
                    <span class="file-icon material-icons">${item.isDir ? 'folder' : 'insert_drive_file'}</span>
                    <span>${item.name}</span>
                `;
                div.onclick = (e) => {
                    if (e.ctrlKey) {
                        div.classList.toggle('selected');
                    } else if (item.isDir) {
                        fetchFiles(div.dataset.path);
                    } else {
                        downloadFile(div.dataset.path);
                    }
                };
                fileList.appendChild(div);
            });
        }

        function updateBreadcrumbs() {
            const breadcrumbs = document.getElementById('breadcrumbs');
            breadcrumbs.innerHTML = '<span onclick="fetchFiles(\'\')">/</span>';
            if (currentPath) {
                const parts = currentPath.split('/');
                let cumulativePath = '';
                parts.forEach((part, index) => {
                    cumulativePath += (index > 0 ? '/' : '') + part;
                    const span = document.createElement('span');
                    span.textContent = ' / ' + part;
                    span.onclick = () => fetchFiles(cumulativePath);
                    breadcrumbs.appendChild(span);
                });
            }
        }

        function createFolder() {
            const folderName = prompt('Enter folder name:');
            if (!folderName) return;
            fetch('file_explorer.php?action=createFolder&path=' + encodeURIComponent(currentPath) + '&name=' + encodeURIComponent(folderName), {
                method: 'POST'
            })
                .then(response => response.text())
                .then(msg => {
                    alert(msg);
                    fetchFiles(currentPath);
                })
                .catch(error => alert('Error creating folder: ' + error));
        }

        function uploadFile() {
            const fileInput = document.getElementById('fileUpload');
            const file = fileInput.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('file', file);
            formData.append('path', currentPath);
            fetch('file_explorer.php?action=upload', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(msg => {
                    alert(msg);
                    fileInput.value = '';
                    fetchFiles(currentPath);
                })
                .catch(error => alert('Error uploading file: ' + error));
        }

        function downloadFile(filePath) {
            const link = document.createElement('a');
            link.href = 'file_explorer.php?action=download&path=' + encodeURIComponent(filePath);
            link.download = filePath.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function cutFile() {
            const selected = document.querySelector('.file-item.selected');
            if (!selected) return alert('Select a file or folder first!');
            clipboard = { type: 'cut', path: selected.dataset.path };
            selected.classList.remove('selected');
            alert('Item cut to clipboard');
        }

        function copyFile() {
            const selected = document.querySelector('.file-item.selected');
            if (!selected) return alert('Select a file or folder first!');
            clipboard = { type: 'copy', path: selected.dataset.path };
            selected.classList.remove('selected');
            alert('Item copied to clipboard');
        }

        function pasteFile() {
            if (!clipboard.path) return alert('Nothing in clipboard!');
            fetch('file_explorer.php?action=' + clipboard.type + '&source=' + encodeURIComponent(clipboard.path) + '&dest=' + encodeURIComponent(currentPath), {
                method: 'POST'
            })
                .then(response => response.text())
                .then(msg => {
                    alert(msg);
                    if (clipboard.type === 'cut') clipboard = { type: null, path: null };
                    fetchFiles(currentPath);
                })
                .catch(error => alert('Error pasting: ' + error));
        }

        function deleteFile() {
            const selected = document.querySelector('.file-item.selected');
            if (!selected) return alert('Select a file or folder first!');
            if (!confirm('Are you sure you want to delete ' + selected.dataset.path.split('/').pop() + '?')) return;
            fetch('file_explorer.php?action=delete&path=' + encodeURIComponent(selected.dataset.path), {
                method: 'POST'
            })
                .then(response => response.text())
                .then(msg => {
                    alert(msg);
                    fetchFiles(currentPath);
                })
                .catch(error => alert('Error deleting: ' + error));
        }

        function shareFile() {
            const selected = document.querySelector('.file-item.selected');
            if (!selected) return alert('Select a file or folder first!');
            const shareLink = `${window.location.origin}/file_explorer.php?action=download&path=${encodeURIComponent(selected.dataset.path)}`;
            prompt('Copy this share link:', shareLink);
        }

        function searchFiles() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const filtered = allFiles.filter(item => item.name.toLowerCase().includes(query));
            renderFileList(filtered);
        }

        fetchFiles();
    </script>
</body>
</html>