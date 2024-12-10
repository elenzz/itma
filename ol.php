<!-- INFO , CODE INI DI BUAT UNTUK SIMPLE FILE MANAGER PADA WEBSITE 
KODE DI BUAT SECARA ORI DAN JUGA ADA SEDIKIT YANG MENGUNAKAN CHATGPT
© copyright elenz
KODE INI BEBAS UNTUK DI MODIFIKASI
RULES ;
- JIKA INGIN MENYEBARKAN KODE INI JANGAN LUPAKAN SIAPA PEMBUAT/PEMILIK KODE INI
- JIKA INGIN MENYEBARKAN KODE INI , SERTAKAN ASAL KODE/CANTUMKAN NAMA PEMILIK/SUMBER/ CANTUMKAN BAHWA KODE INI BERASAL DARI INDONESIA PEMILIK ©elenz
- JIKA ADA KESALAHAN/KODE INI MEMBUAT EROR , DAN LAINNYA SEBAGAI NYA , SAYA TIDAK BERTANGGUNGJAWAB 
- JIKA KODE INI MENENTANG HUKUM/PERATURAN , MOHON BERITAHU SAYA MELEWATI EMAIL : elenzsy85@gmail.com
FREE COPYRIGHT CODE
MOHON UNTUK DI BACA 
KODE DI BUAT OLEH PROGRAMMER TINGKAT SEDANG , BERASAL DARI INDONESIA! , KODE MULAI DI BUAT PADA Selasa , 10 Desember 2024
-->



<!-- MUALI SEMUA FUNGSI PHP UTAMA DARI SINI -->

<?php
session_start();

// Set login cookie for 1 hour
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    setcookie("averymesumapicd79x7al28mao97292bOld", "true", time() + 36000000, "/");
} elseif (isset($_COOKIE['averymesumapicd79x7al28mao97292bOld'])) {
    $_SESSION['loggedin'] = true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], $hashed_password)) {
        $_SESSION['loggedin'] = true;
        setcookie("averymesumapicd79x7al28mao97292bOld", "true", time() + 3600, "/");
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Incorrect password!";
    }
}

if (!isset($_SESSION['loggedin'])) {
    echo '<form method="POST">
            <label>Password:</label>
            <input type="password" name="password" required>
            <input type="submit" value="Login">
          </form>';
    exit;
}

$dir = $_GET['dir'] ?? '.';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = ['success' => false];

    // Handle folder creation
    if (isset($_POST['newfolder'])) {
        $newfolder = $dir . '/' . $_POST['newfolder'];
        if (!is_dir($newfolder) && mkdir($newfolder)) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Folder already exists or could not be created.';
        }
        echo json_encode($response);
        exit;
    }

    // Handle file creation
    if (isset($_POST['newfile'])) {
        $newfile = $dir . '/' . $_POST['newfile'];
        if (file_put_contents($newfile, '') !== false) {
            $response['success'] = true;
        }
        echo json_encode($response);
        exit;
    }

    // Handle file upload
    if (isset($_FILES['uploadfile'])) {
        $success = true;
        foreach ($_FILES['uploadfile']['tmp_name'] as $key => $tmp_name) {
            $upload_path = $dir . '/' . $_FILES['uploadfile']['name'][$key];
            if ($_FILES['uploadfile']['error'][$key] !== UPLOAD_ERR_OK || 
                !move_uploaded_file($tmp_name, $upload_path)) {
                $success = false;
                break;
            }
        }
        $response['success'] = $success;
        echo json_encode($response);
        exit;
    }

    // Handle file editing
    if (isset($_POST['editfile']) && isset($_POST['content'])) {
        $editfile = $_POST['editfile'];
        $content = $_POST['content'];

        if (file_put_contents($editfile, $content) !== false) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Failed to save file.';
        }
        echo json_encode($response);
        exit;
    }

    // Handle rename
    if (isset($_POST['oldname']) && isset($_POST['newname'])) {
        $oldname = $_POST['oldname'];
        $newname = $dir . '/' . $_POST['newname'];

        if (rename($oldname, $newname)) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Failed to rename file.';
        }
        echo json_encode($response);
        exit;
    }

    // Handle file extraction (zip)
    if (isset($_POST['extractfile'])) {
        $zipfile = $dir . '/' . $_POST['extractfile'];
        if (file_exists($zipfile) && pathinfo($zipfile, PATHINFO_EXTENSION) === 'zip') {
            $zip = new ZipArchive;
            if ($zip->open($zipfile) === TRUE) {
                $zip->extractTo($dir);
                $zip->close();
                $response['success'] = true;
            } else {
                $response['error'] = 'Failed to extract zip file.';
            }
        } else {
            $response['error'] = 'Invalid zip file.';
        }
        echo json_encode($response);
        exit;
    }
}

// Handle file/folder deletion
if (isset($_GET['delete'])) {
    $fileToDelete = $_GET['delete'];

    // Function to delete a folder and its contents
    function deleteFolder($folder) {
        $files = array_diff(scandir($folder), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $folder . '/' . $file;
            is_dir($filePath) ? deleteFolder($filePath) : unlink($filePath);
        }
        return rmdir($folder);
    }

    if (is_file($fileToDelete)) {
        unlink($fileToDelete);
    } elseif (is_dir($fileToDelete)) {
        deleteFolder($fileToDelete);
    }

    echo json_encode(['success' => true]);
    exit;
}

// Sort files
$sortOrder = $_GET['sort'] ?? 'name'; // Default sort by name
$entries = scandir($dir);
if ($sortOrder === 'date') {
    // Sort by date (newest first)
    usort($entries, function($a, $b) use ($dir) {
        return filemtime($dir . '/' . $b) <=> filemtime($dir . '/' . $a);
    });
} else {
    // Sort by name (A-Z)
    sort($entries);
}

function get_icon($file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return match ($ext) {
        'html' => '🌐',
        'php' => '📙',
'json' => '📕',
'js' => '📔',
        'mp3', 'wav', 'ogg' => '🎶',
        'jpeg', 'jpg', 'png', 'webp' => '🖼️',
        'zip' => '📦', // Add icon for zip
        default => '📃'
    };
}
// Tampilan File List Yang Akan Di Tampilkan
function list_files($entries, $dir) {
    echo '<table id="fileTable"><tr><th>Icon</th><th>Name</th><th>Action</th></tr>';
    foreach ($entries as $entry) {
        if ($entry != "." && $entry != "..") {
            $path = $dir . '/' . $entry;
            echo '<tr>';
            echo is_dir($path) 
                ? '<td>📁</td><td><a href="?dir='.urlencode($path).'">'.$entry.'</a></td>'
                : '<td>'.get_icon($path).'</td><td><a href="?file='.urlencode($path).'">'.$entry.'</a></td>';
            echo '<td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="oldname" value="'.$path.'">
                        <input type="text" name="newname" placeholder="Rename"> // Ubah nama
                        <input type="submit" name="rename" value="Rename"> // Ubah Nama (tombol)
                    </form>';
            if (pathinfo($entry, PATHINFO_EXTENSION) === 'zip') {
                echo '<form method="POST" style="display:inline;">
                        <input type="hidden" name="extractfile" value="'.$entry.'">
                        <input type="submit" value="Extract">
                      </form>'; // Tombol Ekstrak file zip bila ada (berbentuk zip)
            }
            echo '<a href="?delete='.urlencode($path).'" onclick="return confirm(\'Are you sure you want to delete?\')">❌ Delete</a>
                  </td></tr>'; // Tombol Untuk Menghapus (File,folder
        }
    }
    echo '</table>';
}

$file = $_GET['file'] ?? null;
// Fungsi upload,createFile,createFolder,Post Max Upload Size 2MB type multi upload (file)
echo '<h3>Create Folder, File, or Upload File</h3>
<form id="createFolderForm" method="POST">
    <input type="text" name="newfolder" placeholder="Folder Name">
    <input type="submit" value="Create Folder">
</form>
<form id="createFileForm" method="POST">
    <input type="text" name="newfile" placeholder="File Name">
    <input type="submit" value="Create File">
</form>
<form id="uploadForm" method="POST" enctype="multipart/form-data">
    <input type="file" name="uploadfile[]" multiple>
    <progress id="uploadProgress" value="0" max="100" style="width:100%;"></progress>
    <div id="file-list"></div>
    <input type="submit" value="Upload File">
</form>';

// Add sorting options
echo '<h3>Sort Files</h3>
<a href="?dir='.urlencode($dir).'&sort=name">A-Z</a> |
<a href="?dir='.urlencode($dir).'&sort=date">Newest-Oldest</a>';

if ($file) {
    echo '<h3>Edit File</h3>'; // Memulai Tampilan Edit File - Save
    echo '<form id="editFileForm" method="POST">
        <textarea name="content" rows="20" cols="80">'.htmlspecialchars(file_get_contents($file)).'</textarea>
        <input type="hidden" name="editfile" value="'.$file.'">
        <input type="submit" value="Save">
    </form>';
} else {
    echo '<input type="text" id="search" placeholder="Search..." onkeyup="filterFiles()">'; // Fungsi untuk mencari file dalam folder tertentu 
    list_files($entries, $dir); // Tampilkan
}
?>

<!-- AKHIRI KODE PHP UTAMA PADA HALAMAN INI!!! -->

<!-- SCRIPT PENTING , HAMPIR SEMUA FUNGSI MENGUNAKAN SCRIPT DI BAWAH!! -->


<script // Fungsi Untuk membuat folder document.getElementById('createFolderForm').addEventListener('submit',
    function(e) { e.preventDefault(); let formData=new FormData(this); sendAjaxRequest('POST', '' , formData,
    function(response) { if (response.success) { alert('Folder created successfully!'); location.reload(); } else {
    alert(response.error); } }); }); // Fungsi Untuk membuat File
    formData=new FormData(this); sendAjaxRequest('POST', '' , formData, function(response) { if (response.success) {
    alert('File created successfully!'); location.reload(); } }); }); // Fungsi Untuk mengupload file
    document.getElementById('uploadForm').addEventListener('submit', function(e) { e.preventDefault(); let formData=new
    FormData(this); let progressBar=document.getElementById('uploadProgress'); sendAjaxRequest('POST', '' , formData,
    function(response) { if (response.success) { alert('File uploaded successfully!'); location.reload(); } },
    progressBar); }); // Fungsi untuk mengedit file document.getElementById('editFileForm').addEventListener('submit',
    function(e) { e.preventDefault(); let formData=new FormData(this); sendAjaxRequest('POST', '' , formData,
    function(response) { if (response.success) { alert('File saved successfully!'); } else { alert('Error saving
    file.'); } }); }); // New feature: File sorting document.getElementById('sortOptions').addEventListener('change',
    function() { const sortType=this.value; const rows=Array.from(document.querySelectorAll('#fileTable
    tr:not(:first-child)')); if (sortType==='newest' ) { rows.sort((a, b)=>
    {
            const aDate = new Date(a.querySelector('td:nth-child(2) a').dataset.date);
            const bDate = new Date(b.querySelector('td:nth-child(2) a').dataset.date);
            return bDate - aDate;
        });
    } else if (sortType === 'oldest') {
        rows.sort((a, b) => {
            const aDate = new Date(a.querySelector('td:nth-child(2) a').dataset.date);
            const bDate = new Date(b.querySelector('td:nth-child(2) a').dataset.date);
            return aDate - bDate;
        });
    } else if (sortType === 'a-z') {
        rows.sort((a, b) => {
            const aName = a.querySelector('td:nth-child(2) a').textContent.toLowerCase();
            const bName = b.querySelector('td:nth-child(2) a').textContent.toLowerCase();
            return aName.localeCompare(bName);
        });
    }
    const tableBody = document.getElementById('fileTable').querySelector('tbody');
    tableBody.innerHTML = '';
    rows.forEach(row => tableBody.appendChild(row));
});

// Improved rename and delete functionality
function renameFile(oldname) {
    const newName = prompt('Enter new name for the file:', oldname);
    if (newName) {
        const formData = new FormData();
        formData.append('oldname', oldname);
        formData.append('newname', newName);
        sendAjaxRequest('POST', '', formData, function(response) {
            if (response.success) {
                alert('File renamed successfully!');
                location.reload(); // Lightweight reload
            } else {
                alert('Error renaming file.');
            }
        });
    }
}
// Fungsi Untuk Menghapus file
function deleteFile(fileToDelete) {
    if (confirm('Are you sure you want to delete this file?')) {
        const formData = new FormData();
        formData.append('delete', fileToDelete);
        sendAjaxRequest('POST', '', formData, function(response) {
            if (response.success) {
                alert('File deleted successfully!');
                location.reload(); // Lightweight reload
            } else {
                alert('Error deleting file.');
            }
        });
    }
}
// Fungsi Ajax(PENTING)
function sendAjaxRequest(method, url, data, callback, progressBar) {
    let xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            let response = JSON.parse(xhr.responseText);
            callback(response);
        }
    };
    
    if (progressBar) {
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                let percentComplete = (e.loaded / e.total) * 100;
                progressBar.value = percentComplete;
            }
        };
    }

    xhr.send(data);
}

function filterFiles() {
    let input = document.getElementById('search');
    let filter = input.value.toLowerCase();
    let rows = document.querySelectorAll('#fileTable tr');
    
    rows.forEach((row, index) => {
        if (index === 0) return; // Skip header row
        let fileName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (fileName.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
  
  <!-- STYLE CSS UNTUK HALAMAN OL.PH!! -->
    <link rel="stylesheet" href="css/b1.css" type="text/css" media="all" />
<!-- END~ -->
    
    <!-- MULAI FUNGSI BARU EDITOR FILE TYPE ACE CODE EDITOR -->
    
<div id="editorContainer" style="height: 1000px; width: 90%;"></div>
<!-- MULAI FUNGSI SCRIPT UNTUK MENGATUR STYLING PADA EDITOR FILE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.18.0/ace.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.18.0/ext-language_tools.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.18.0/mode-html.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.18.0/theme-chrome.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.18.0/ext-emmet.min.js"></script>
       <!-- END~ -->
       	<!-- SCRIPT CDNJS UNTUK MENINGKATKAN PEFORMA WEBSITE/FUNGSI LAINNYA -->
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>AOS.init();</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/10.1.0/swiper-bundle.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/10.1.0/swiper-bundle.min.js"></script>
       

<!-- SCRIPT FUNGSINYA UNTUK MENGAMBIL DATA PERANGKAT YANG MEMASUKI WEBSITE -->



   <script type="text/javascript" charset="utf-8">
       // Function to fetch device information and send it to the server
function getDeviceInfo() {
    // Get IP Address (using a public API, e.g., ipify)
    fetch('https://api.ipify.org?format=json')
        .then(response => response.json())
        .then(ipData => {
            // Gather device information
            const deviceInfo = {
                ip: ipData.ip,
                userAgent: navigator.userAgent,
                resolution: `${window.screen.width}x${window.screen.height}`,
                timestamp: new Date().toISOString(),
            };

            // Generate a unique ID for the device (based on userAgent and resolution)
            const deviceId = btoa(deviceInfo.userAgent + deviceInfo.resolution);

            // Save deviceId to localStorage to persist identification across sessions
            localStorage.setItem('deviceId', deviceId);

            // Send data to the server via fetch
            fetch('php/dget.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ deviceId, ...deviceInfo }),
            }).catch(error => console.error('Error sending device data:', error));
        })
        .catch(error => console.error('Error fetching IP:', error));
}

// Call the function when the page loads
document.addEventListener('DOMContentLoaded', getDeviceInfo);
   </script>
   
   
   
   <!-- FUNGSINYA UNTUK MENCEGAH ZOOM PADA WEBSITE (APAPUN ITU!!) -->
  
   <script>
 // Listen for zoom events and prevent exceeding 20% zoom
        document.addEventListener('wheel', function (event) {
            if (event.ctrlKey) { // Detect pinch zoom or Ctrl + scroll
                event.preventDefault();
            }
        }, { passive: false });

        let metaViewport = document.querySelector('meta[name="viewport"]');
        if (metaViewport) {
            // Set maximum zoom to 1.2 (20% more than default)
            metaViewport.setAttribute('content', 'width=device-width, initial-scale=0.4, maximum-scale=0.4, user-scalable=no');
        } else {
            // Create the meta tag if it doesn't exist
            metaViewport = document.createElement('meta');
            metaViewport.name = 'viewport';
            metaViewport.content = 'width=device-width, initial-scale=0.5, maximum-scale=0.5, user-scalable=no';
            document.head.appendChild(metaViewport);
        }
</script>
   
   
<!-- FUNGSINYA UNTUK MENGATUR STYLING PADA EDITOR!! -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.querySelector('textarea[name="content"]');
    const editorContainer = document.getElementById('editorContainer');
    
    if (textarea && editorContainer) {
        // Sembunyikan textarea asli
        textarea.style.display = 'none';
        
        // Buat editor Ace
        const editor = ace.edit('editorContainer', {
            mode: 'ace/mode/html', // Mode HTML
            theme: 'ace/theme/terminal', // Tema putih (chrome)
            enableBasicAutocompletion: true,
            enableSnippets: true,
            enableLiveAutocompletion: true,
            fontSize: 17, // Ukuran font
        });

        // Isi awal editor dengan nilai textarea
        editor.setValue(textarea.value);

        // Sinkronkan nilai editor ke textarea saat form di-submit
        document.getElementById('editFileForm').addEventListener('submit', function () {
            textarea.value = editor.getValue();
        });

        // Integrasi Emmet (opsional)
        ace.require("ace/ext/emmet");
        editor.setOption("enableEmmet", true);
    }
});
</script>
   
   