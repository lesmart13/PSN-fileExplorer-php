<?php header('Content-Type: text/html; charset=UTF-8'); ?>
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
            background: transparent;
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
        .controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .search-bar {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 0.5rem 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            min-width: 150px;
            max-width: 400px;
            overflow: hidden;
        }
        .search-bar input {
            border: none;
            outline: none;
            font-size: 1rem;
            width: 100%;
            background: transparent;
            resize: horizontal;
            min-width: 100px;
            max-width: 100%;
            padding: 0 0.5rem;
        }
        .view-select {
            padding: 0.5rem;
            border-radius: 8px;
            border: 1px solid #1976d2;
            background: rgba(255, 255, 255, 0.9);
            color: #1976d2;
            font-size: 0.9rem;
        }
        .file-list {
            display: grid;
            gap: 1rem;
            padding: 1rem;
            transition: all 0.3s ease;
        }
        .file-list.small {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        }
        .file-list.medium {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        }
        .file-list.large {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
        .file-list.small .file-icon { font-size: 1.2rem; }
        .file-list.large .file-icon { font-size: 1.8rem; }
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
            transition: transform 0.2s, background 0.2s, opacity 0.2s;
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
        .fab.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .context-menu {
            position: absolute;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 30;
            display: none;
            padding: 0.5rem 0;
            max-width: 200px;
        }
        .context-menu div {
            padding: 0.5rem 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }
        .context-menu div:hover {
            background: #f5f5f5;
        }
        @media (max-width: 1024px) {
            .file-list.small { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
            .file-list.medium { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
            .file-list.large { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); }
        }
        @media (max-width: 768px) {
            .file-list.small { grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); }
            .file-list.medium { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
            .file-list.large { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
            .header { padding: 0.75rem; }
            .fab { width: 40px; height: 40px; }
        }
        @media (max-width: 480px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                padding: 0.5rem;
            }
            .controls {
                width: 100%;
                justify-content: space-between;
            }
            .search-bar {
                max-width: 100%;
                width: 100%;
                margin-top: 0.5rem;
            }
            .file-list {
                grid-template-columns: 1fr !important;
            }
            .file-item { padding: 0.75rem; }
            .fab-container {
                bottom: 0.5rem;
                right: 0.5rem;
                gap: 0.5rem;
            }
            .fab { width: 36px; height: 36px; }
            .file-icon { font-size: 1.2rem !important; }
            .context-menu { max-width: 150px; }
        }
    </style>
</head>
<body oncontextmenu="return false;">
    <div class="container">
        <div class="header">
            <div class="breadcrumbs" id="breadcrumbs">
                <span onclick="fetchFiles('')">/</span>
            </div>
            <div class="controls">
                <div class="search-bar">
                    <span class="material-icons">search</span>
                    <input type="text" id="searchInput" placeholder="Search files..." oninput="searchFiles()">
                </div>
                <select class="view-select" onchange="changeViewSize(this.value)">
                    <option value="medium">Medium</option>
                    <option value="small">Small</option>
                    <option value="large">Large</option>
                </select>
            </div>
        </div>
        <div id="fileList" class="file-list medium"></div>
    </div>
    <div class="fab-container">
        <button class="fab" onclick="createFolder()" title="New Folder">
            <span class="material-icons">create_new_folder</span>
        </button>
        <label class="fab secondary" title="Upload File">
            <span class="material-icons">upload_file</span>
            <input type="file" id="fileUpload" onchange="uploadFile()" style="display: none;">
        </label>
        <button id="cutBtn" class="fab secondary hidden" onclick="cutFile()" title="Cut">
            <span class="material-icons">content_cut</span>
        </button>
        <button id="copyBtn" class="fab secondary hidden" onclick="copyFile()" title="Copy">
            <span class="material-icons">content_copy</span>
        </button>
        <button id="pasteBtn" class="fab secondary hidden" onclick="pasteFile()" title="Paste">
            <span class="material-icons">content_paste</span>
        </button>
        <button id="deleteBtn" class="fab secondary hidden" onclick="deleteFile()" title="Delete">
            <span class="material-icons">delete</span>
        </button>
        <button id="downloadBtn" class="fab secondary hidden" onclick="downloadSelectedFile()" title="Download">
            <span class="material-icons">download</span>
        </button>
        <button class="fab secondary" onclick="shareFile()" title="Share">
            <span class="material-icons">share</span>
        </button>
    </div>
    <div id="contextMenu" class="context-menu"></div>

    <script>
        let currentPath = '';
        let clipboard = { type: null, path: null };
        let allFiles = [];
        let selectedFile = null;

        document.body.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            const fileItem = e.target.closest('.file-item');
            if (fileItem) {
                showFileContextMenu(e, fileItem);
            } else {
                showAppContextMenu(e);
            }
        });

        document.addEventListener('click', (e) => {
            const fileItem = e.target.closest('.file-item');
            const contextMenu = document.getElementById('contextMenu');
            const fab = e.target.closest('.fab');
            if (!fileItem && !contextMenu.contains(e.target) && !fab && selectedFile) {
                selectedFile.classList.remove('selected');
                selectedFile = null;
                updateFABVisibility();
            }
        });

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
                div.dataset.isDir = item.isDir;
                div.innerHTML = `
                    <span class="file-icon material-icons">${item.isDir ? 'folder' : 'insert_drive_file'}</span>
                    <span>${item.name}</span>
                `;
                div.onclick = (e) => {
                    if (e.ctrlKey) {
                        toggleSelection(div);
                    } else {
                        if (item.isDir) {
                            fetchFiles(div.dataset.path);
                        } else {
                            toggleSelection(div);
                        }
                    }
                };
                fileList.appendChild(div);
            });
            updateFABVisibility();
        }

        function toggleSelection(div) {
            if (selectedFile && selectedFile !== div) {
                selectedFile.classList.remove('selected');
            }
            div.classList.toggle('selected');
            selectedFile = div.classList.contains('selected') ? div : null;
            updateFABVisibility();
        }

        function updateFABVisibility() {
            const hasSelection = !!selectedFile;
            const hasClipboard = !!clipboard.path;
            const isFileSelected = hasSelection && selectedFile.dataset.isDir === 'false';
            document.getElementById('cutBtn').classList.toggle('hidden', !hasSelection);
            document.getElementById('copyBtn').classList.toggle('hidden', !hasSelection);
            document.getElementById('pasteBtn').classList.toggle('hidden', !hasClipboard);
            document.getElementById('deleteBtn').classList.toggle('hidden', !hasSelection);
            document.getElementById('downloadBtn').classList.toggle('hidden', !isFileSelected);
        }

        function adjustMenuPosition(menu, x, y) {
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const menuWidth = menu.offsetWidth;
            const menuHeight = menu.offsetHeight;

            let adjustedX = x;
            let adjustedY = y;

            if (x + menuWidth > viewportWidth) {
                adjustedX = viewportWidth - menuWidth - 10;
            }
            if (adjustedX < 0) adjustedX = 0;

            if (y + menuHeight > viewportHeight) {
                adjustedY = viewportHeight - menuHeight - 10;
            }
            if (adjustedY < 0) adjustedY = 0;

            menu.style.left = `${adjustedX}px`;
            menu.style.top = `${adjustedY}px`;
        }

        function showFileContextMenu(e, div) {
            selectedFile = div;
            if (!div.classList.contains('selected')) toggleSelection(div);
            const menu = document.getElementById('contextMenu');
            menu.innerHTML = `
                <div onclick="cutFile()"><span class="material-icons">content_cut</span>Cut</div>
                <div onclick="copyFile()"><span class="material-icons">content_copy</span>Copy</div>
                <div onclick="pasteFile()"><span class="material-icons">content_paste</span>Paste</div>
                <div onclick="deleteFile()"><span class="material-icons">delete</span>Delete</div>
                ${div.dataset.isDir === 'false' ? '<div onclick="downloadSelectedFile()"><span class="material-icons">download</span>Download</div>' : ''}
            `;
            menu.style.display = 'block';
            adjustMenuPosition(menu, e.pageX, e.pageY);
            document.addEventListener('click', hideContextMenu, { once: true });
        }

        function showAppContextMenu(e) {
            const menu = document.getElementById('contextMenu');
            menu.innerHTML = `
                <div onclick="createFolder()"><span class="material-icons">create_new_folder</span>New Folder</div>
                <div onclick="document.getElementById('fileUpload').click()"><span class="material-icons">upload_file</span>Upload File</div>
                <div onclick="alert('Folder upload not implemented yet')"><span class="material-icons">folder</span>Upload Folder</div>
                <div onclick="alert('Personalize feature coming soon!')"><span class="material-icons">palette</span>Personalize</div>
                <div onclick="alert('Settings feature coming soon!')"><span class="material-icons">settings</span>Settings</div>
            `;
            menu.style.display = 'block';
            adjustMenuPosition(menu, e.pageX, e.pageY);
            document.addEventListener('click', hideContextMenu, { once: true });
        }

        function hideContextMenu() {
            document.getElementById('contextMenu').style.display = 'none';
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

        function changeViewSize(size) {
            const fileList = document.getElementById('fileList');
            fileList.className = `file-list ${size}`;
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

        function downloadSelectedFile() {
            if (!selectedFile || selectedFile.dataset.isDir === 'true') return alert('Select a file to download!');
            downloadFile(selectedFile.dataset.path);
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
            if (!selectedFile) return alert('Select a file or folder first!');
            clipboard = { type: 'cut', path: selectedFile.dataset.path };
            selectedFile.classList.remove('selected');
            selectedFile = null;
            alert('Item cut to clipboard');
            updateFABVisibility();
        }

        function copyFile() {
            if (!selectedFile) return alert('Select a file or folder first!');
            clipboard = { type: 'copy', path: selectedFile.dataset.path };
            selectedFile.classList.remove('selected');
            selectedFile = null;
            alert('Item copied to clipboard');
            updateFABVisibility();
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
            if (!selectedFile) return alert('Select a file or folder first!');
            if (!confirm('Are you sure you want to delete ' + selectedFile.dataset.path.split('/').pop() + '?')) return;
            fetch('file_explorer.php?action=delete&path=' + encodeURIComponent(selectedFile.dataset.path), {
                method: 'POST'
            })
                .then(response => response.text())
                .then(msg => {
                    alert(msg);
                    selectedFile = null;
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
            const query = document.getElementById('searchInput').value.trim();
            if (!query) {
                fetchFiles(currentPath);
                return;
            }
            fetch('file_explorer.php?action=search&query=' + encodeURIComponent(query) + '&path=' + encodeURIComponent(currentPath))
                .then(response => {
                    if (!response.ok) throw new Error('Search failed');
                    return response.json();
                })
                .then(data => {
                    allFiles = data;
                    renderFileList(data);
                })
                .catch(error => alert('Error searching files: ' + error));
        }

        fetchFiles();
    </script>
</body>
</html>