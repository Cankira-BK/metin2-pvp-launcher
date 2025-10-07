<?php
require 'config.php'; if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; } // config.php dosyası kontrol edilir ve kullanıcı giriş yapmadı ise yönlendirilir.


// Kullanıcının dosya yönetim yetkisini veritabanından al
$stmt = $pdo->prepare("SELECT dosya_yonetim FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$aktif = $stmt->fetch();

// Ana yükleme dizini ve çöp kutusu dizini ayarları
$base_dir  = $BASE_DIR;
$trash_dir = $TRASH_DIR;
if (!is_dir($trash_dir)) mkdir($trash_dir, 0777, true);

// GET parametresinden alt dizin seçimi
$dir = isset($_GET['dir']) ? trim($_GET['dir'], '/') : '';
$upload_dir = $base_dir . ($dir ? '/' . $dir : '');
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// Çöp kutusu görünüm kontrolü
$view_trash = isset($_GET['view_trash']);

// --- Çöp Kutusundan Geri Yükle ---
if ($view_trash && isset($_POST['restore'])) {
    $target = $trash_dir . '/' . basename($_POST['restore']);
    $restore_path = $upload_dir . '/' . basename($_POST['restore']);
    rename($target, $restore_path);
}

// --- Çöp Kutusundan Kalıcı Sil ---
if ($view_trash && isset($_POST['trash_permadelete'])) {
    $target = $trash_dir . '/' . basename($_POST['trash_permadelete']);
    if (is_file($target)) {
        unlink($target);
    } elseif (is_dir($target)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $sub) {
            $sub->isDir() ? rmdir($sub->getRealPath()) : unlink($sub->getRealPath());
        }
        rmdir($target);
    }
}

// --- Tekli kalıcı silme ---
if (isset($_POST['permadelete'])) {
    $target = $upload_dir . '/' . basename($_POST['permadelete']);
    if (is_file($target)) {
        unlink($target);
    } elseif (is_dir($target)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $sub) {
            $sub->isDir() ? rmdir($sub->getRealPath()) : unlink($sub->getRealPath());
        }
        rmdir($target);
    }
	   
}

// ----------- crclist düzenleme (editör) işlemleri ----------
$editing_crclist = (isset($_GET['edit']) && $_GET['edit'] === 'crclist');
$crclist_path = "$upload_dir/crclist";
$edit_msg = "";
if ($editing_crclist) {
    if (!file_exists($crclist_path)) {
        $edit_msg = "crclist dosyası bulunamadı!";
        $crclist_content = '';
    } elseif (!is_writable($crclist_path)) {
        $edit_msg = "crclist dosyası yazılamıyor!";
        $crclist_content = file_get_contents($crclist_path);
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crclist_edit'])) {
            file_put_contents($crclist_path, $_POST['crclist_content']);
            $edit_msg = "Başarıyla kaydedildi!";
        }
        $crclist_content = file_get_contents($crclist_path);
    }
}
// ---------------------------------------------------------

// Dosya yukleme
if (isset($_FILES['files']) && !$editing_crclist) {
    foreach ($_FILES['files']['name'] as $index => $name) {
        $filename = basename($name);
        $tmp = $_FILES['files']['tmp_name'][$index];
        if (is_uploaded_file($tmp)) {
            move_uploaded_file($tmp, "$upload_dir/$filename");
        }
    }
}

// Zip cikarma
if (isset($_POST['unzip']) && !$editing_crclist) {
    $zip_file = $upload_dir . '/' . basename($_POST['unzip']);
    $zip = new ZipArchive;
    if ($zip->open($zip_file) === TRUE) {
        $zip->extractTo($upload_dir);
        $zip->close();
    }
}

// Toplu islem (sil/kopyala/tasi/copy)
if (isset($_POST['bulk_action']) && isset($_POST['selected_items']) && !$editing_crclist) {
    $action = $_POST['bulk_action'];
    $target_dir = isset($_POST['target_dir']) ? trim($_POST['target_dir'], '/') : $dir;
    $target_path = $base_dir . ($target_dir ? '/' . $target_dir : '');
    if (!is_dir($target_path)) mkdir($target_path, 0777, true);
    foreach ($_POST['selected_items'] as $item) {
        $src = "$upload_dir/" . basename($item);
        $dst = "$target_path/" . basename($item);
        $trash_path = "$trash_dir/" . ($dir ? "$dir/" : '') . basename($item);
        if (!is_dir(dirname($trash_path))) mkdir(dirname($trash_path), 0777, true);

        if ($action === 'delete') {
            if (is_file($src)) unlink($src);
            if (is_dir($src)) {
                $items = array_diff(scandir($src), ['.', '..']);
                foreach ($items as $subitem) {
                    if (is_file("$src/$subitem")) unlink("$src/$subitem");
                }
                rmdir($src);
            }
        }
        elseif ($action === 'copy') {
            if (is_file($src)) copy($src, $dst);
        }
        elseif ($action === 'move') {
            rename($src, $dst);
        }
        elseif ($action === 'trash') {
            rename($src, $trash_path);
        }
    }
}

// Çöp kutusunda toplu işlemler
if ($view_trash && isset($_POST['bulk_action']) && isset($_POST['selected_items'])) {
    $action = $_POST['bulk_action'];
    foreach ($_POST['selected_items'] as $item) {
        $target = "$trash_dir/" . basename($item);
        $restore_path = "$upload_dir/" . basename($item);

        if ($action === 'restore') {
            rename($target, $restore_path);
        }
        elseif ($action === 'trashdelete') { // kalıcı sil
            if (is_file($target)) {
                unlink($target);
            } elseif (is_dir($target)) {
                $items = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($items as $sub) {
                    $sub->isDir() ? rmdir($sub->getRealPath()) : unlink($sub->getRealPath());
                }
                rmdir($target);
            }
        }
    }
}


// Tekli silme (çöp kutusuna gönder)
if (isset($_POST['delete']) && !$editing_crclist) {
    $target = $upload_dir . '/' . basename($_POST['delete']);
    $trash_path = "$trash_dir/" . ($dir ? "$dir/" : '') . basename($_POST['delete']);
    if (!is_dir(dirname($trash_path))) mkdir(dirname($trash_path), 0777, true);
    rename($target, $trash_path);
}

// Klasor olusturma
if (isset($_POST['new_folder']) && !$editing_crclist) {
    $folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['new_folder']);
    if ($folder_name) {
        mkdir("$upload_dir/$folder_name", 0777, true);
    }
}

//Dosyaları listelemek ve navigasyon (breadcrumb) oluşturmak.
$files = array_diff(scandir($upload_dir), ['.', '..']);
$relative = $dir ? $dir . '/' : '';
function buildBreadcrumb($dir) {
    $parts = explode('/', $dir);
    $path = '';
    $breadcrumbs = ['<a href="?">/</a>'];
    foreach ($parts as $part) {
        if (!$part) continue;
        $path .= ($path ? '/' : '') . $part;
        $breadcrumbs[] = "<a href=\"?dir=" . urlencode($path) . "\">" . htmlspecialchars($part) . "</a>";
    }
    return implode(' / ', $breadcrumbs);
}

// Listeleme için kullanılacak dizin
$current_dir = $view_trash ? $trash_dir : $upload_dir;
$files = array_diff(scandir($current_dir), ['.', '..']);

// İkon eşleştirme fonksiyonu
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        "zip" => "📦", "rar" => "📦",  "lz" => "📦",  "7z" => "📦",
        "png" => "🖼️", "jpg" => "🖼️", "jpeg" => "🖼️", "gif" => "🖼️",
        "txt" => "📄", "pdf" => "📄", "doc" => "📄", "docx" => "📄",
        "xls" => "📊", "xlsx" => "📊", "ppt" => "📊", "pptx" => "📊"
    ];
    return isset($icons[$ext]) ? $icons[$ext] : "📁";
}

// Klasör boyutu hesaplama
function folderSize($dir) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

// Boyutu okunabilir formata çevir
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

//Dosya Yükleme Progress Bar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file']['name'])) {
    $uploadDir = "uploads/";
    $uploadFile = $uploadDir . basename($_FILES['file']['name']);
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo "Dosya başarıyla yüklendi: " . $uploadFile;
    } else {
        echo "Dosya yükleme hatası.";
    }
    exit; // Ajax cevabı burada bitsin
}

?>



<!DOCTYPE html>
<html>
<head>
  <title>Dosya Yükle</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 
</head>
<body>
<?php include "sidebar.php"; ?>
<div class="content">
  
  <h2>📁 Dosya Yükle</h2>
  
  <?php if ($aktif['dosya_yonetim'] == 1): ?>
  
<div class="mb-3">
  <strong>Klasör Yolu:</strong> 
  <?= $dir === '' ? 'Ana Dizin' : buildBreadcrumb($dir) ?>
  <?php if ($dir): ?>
    <?php $parent = dirname($dir); ?>
    <a href="?dir=<?= urlencode($parent === '.' ? '' : $parent) ?>" class="btn btn-sm btn-secondary ms-3">⬅️ Geri</a>
  <?php endif; ?>
</div>


  <?php if ($editing_crclist): ?>
    <div class="alert alert-secondary">
        <h5>crclist Dosyasını Düzenle</h5>
        <?php if ($edit_msg) echo '<div class="mb-2" style="color:#fa5252;">'.$edit_msg.'</div>'; ?>
        <form method="post">
            <textarea name="crclist_content" class="form-control" rows="14"><?= htmlspecialchars($crclist_content ?? '') ?></textarea>
            <button type="submit" name="crclist_edit" class="btn btn-success mt-3">Kaydet</button>
            <a href="upload.php<?= $dir ? '?dir='.urlencode($dir) : '' ?>" class="btn btn-secondary mt-3">Vazgeç</a>
        </form>
    </div>
  <?php else: ?>


  <form method="POST" enctype="multipart/form-data" class="mb-4">
    <div class="mb-2">
      <input type="file" name="files[]" class="form-control" multiple required>
      <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
    </div>	
    <button class="btn btn-success">Yükle</button>
  </form>
  
  <!-- Progress Bar -->
<div id="progressContainer" style="width:100%;background:#f3f3f3;border-radius:8px;overflow:hidden;margin-top:10px;display:none;">
  <div id="progressBar" style="width:0;height:25px;line-height:25px;background:linear-gradient(90deg,#4caf50,#81c784);color:white;text-align:center;font-weight:bold;">
    0%
  </div>
</div>
<div id="status" class="mt-2"></div>

  

     <!-- Çöp Kutusu Butonu -->
      <?php if ($view_trash): ?>
        <a href="upload.php<?= $dir ? '?dir='.urlencode($dir) : '' ?>" class="btn btn-secondary mb-3">Ana Dizine Dön</a>
      <?php else: ?>
        <a href="upload.php?view_trash=1" class="btn btn-warning mb-3">Çöp Kutusunu Görüntüle</a>
      <?php endif; ?>

  <form method="POST" class="mb-4 d-flex" style="gap: 10px;">
    <input type="text" name="new_folder" class="form-control" placeholder="Yeni klasör adı" required>
    <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
    <button class="btn btn-primary">Klasör Oluştur</button>
  </form>

  <form method="POST" class="mb-4">
    <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
    <div class="d-flex gap-2 align-items-center">
      <?php if ($view_trash): ?>
	  <!-- Çöp Kutusu Toplu İşlem Menü -->
    <select name="bulk_action" class="form-select w-auto">
        <option value="">Toplu İşlem Seç</option>
        <option value="restore">Geri Yükle</option>
        <option value="trashdelete">Kalıcı Sil</option>
    </select>
<?php else: ?>
		<!-- Ana Dizin Toplu İşlem Menüsü -->
    <select name="bulk_action" class="form-select w-auto">
        <option value="">Toplu İşlem Seç</option>
        <option value="delete">Kalıcı Sil</option>
        <option value="copy">Kopyala</option>
        <option value="move">Taşı</option>
        <option value="trash">Çöp Kutusuna Gönder</option>
    </select>
    <input type="text" name="target_dir" placeholder="Hedef klasör" class="form-control w-auto">
<?php endif; ?>
      <button type="button" class="btn btn-primary" onclick="bulkConfirm(this)">Uygula</button>
    </div>
    <br>
		<!-- Arama kutusu -->	
	<input type="text" id="searchBox" placeholder="Ara..." class="form-control mb-2">
	
    <table class="table">
      <thead>
        <tr>
          <th><input type="checkbox" onclick="document.querySelectorAll('input[name=\'selected_items[]\']').forEach(cb=>cb.checked=this.checked)"></th>
          <th>Ad</th>
          <th>İşlemler</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($files as $f): ?>
    <?php 
        $path = "$upload_dir/$f"; 
        if (is_dir($path)) {
            $icon = getFileIcon($f); 
            $size = formatSize(folderSize($path));
        } else {
            $icon = getFileIcon($f); 
            $size = formatSize(filesize($path));
        }
    ?>
    <tr>
        <td><input type="checkbox" name="selected_items[]" value="<?= htmlspecialchars($f) ?>"></td>
        <td>
            <?php if (is_dir($path)): ?>
                <a href="?dir=<?= urlencode($relative . $f) ?>" class="folder">
                    <?= $icon ?> <?= htmlspecialchars($f) ?>
                </a>
            <?php else: ?>
                <?= $icon ?> <?= htmlspecialchars($f) ?>
            <?php endif; ?>
        </td>
        <td><?= $size ?></td>
        <td class="file-actions">
            <!-- buradaki butonlar/düğmeler aynen devam edecek -->

		  
		  
            <?php if ($view_trash): ?>
              <!-- Çöp kutusunda: Geri Yükle + Kalıcı Sil -->
              <form method="post" style="display:inline;">
                <button type="submit" name="restore" value="<?= htmlspecialchars($f) ?>" class="btn btn-sm btn-success">Geri Yükle</button>
              </form>
              <form method="post" style="display:inline;">
				<input type="hidden" name="trash_permadelete" value="<?= htmlspecialchars($f) ?>">
				<button type="button" class="btn btn-sm btn-dark"
					onclick="confirmDelete(this, '<?= htmlspecialchars($f) ?>')">
					Kalıcı Sil
				</button>
			</form>
            <?php else: ?>
              <?php if (!is_dir($path) && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'zip'): ?>
                <form method="POST">
                  <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
                  <button name="unzip" value="<?= htmlspecialchars($f) ?>" class="btn btn-sm btn-warning">Unzip</button>
                </form>
              <?php endif; ?>
              <?php if ($f === 'crclist' && !is_dir($path)): ?>
                  <a href="upload.php?edit=crclist<?= $dir ? '&dir='.urlencode($dir) : '' ?>" class="btn btn-sm btn-info">Düzenle</a>
              <?php endif; ?>
              <form method="POST" action="upload.php" onsubmit="return confirm('Silmek istediğinize emin misiniz?');" style="display:inline;">
                <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
                <button type="submit" name="delete" value="<?= htmlspecialchars($f) ?>" class="btn btn-sm btn-danger">Çöp Kutusu</button>
              </form>
              <form method="post" style="display:inline;">
                <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
                <?php $formId = 'perm_form_' . md5($f); ?>
				<!-- ANA DİZİN KALICI SİL BUTONU -->
			<form method="post" style="display:inline;">
				<input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
				<input type="hidden" name="permadelete" value="<?= htmlspecialchars($f) ?>">
				<button type="button" class="btn btn-sm btn-dark"
					onclick="confirmDelete(this, '<?= htmlspecialchars($f) ?>')">
					Kalıcı Sil
				</button>
			</form>






              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </form>

  <?php endif; ?>
  
  
  
  
  <?php else: ?>

  <!-- KULLANICI YETKİLENDİRMESİ "0" İSE AŞAĞIDAKİ ALANI GÖRECEK -->
  <h4>Mevcut Dosyalar</h4>
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>Ad</th>
          <th>Boyut</th>
          <th>Tarih</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $files = array_diff(scandir($upload_dir), ['.', '..']);
        foreach ($files as $f):
            $path = $upload_dir . '/' . $f;
        ?>
        <tr>
          <td><?= htmlspecialchars($f) ?></td>
          <td><?= is_file($path) ? filesize($path) : '-' ?></td>
          <td><?= date('Y-m-d H:i:s', filemtime($path)) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

  
  
  
  
</div>

<!-- SweetAlert2 Script -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(button, filename) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: filename + ' kalıcı olarak silinecek! Bu işlem geri alınamaz.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'Vazgeç'
    }).then((result) => {
        if (result.isConfirmed) {
            button.closest("form").submit(); // 🔑 butonun kendi formunu buluyor
        }
    });
}
</script>

<!-- Toplu İşlemler Buton Scripti--!>
<script>
function bulkConfirm(button) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: 'Seçilen işlem uygulanacak. Devam edilsin mi?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet',
        cancelButtonText: 'Vazgeç'
    }).then((result) => {
        if (result.isConfirmed) {
            button.closest("form").submit();
        }
    });
}
</script>

<!-- Arama Kutusu Scripti--!>
<script>
function debounce(func, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

function filterRows() {
    let filter = document.getElementById("searchBox").value.toLowerCase();
    document.querySelectorAll("table tbody tr").forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
    });
}

document.getElementById("searchBox")
    .addEventListener("keyup", debounce(filterRows, 300));
</script>


<!-- Dosya Yükleme+Progress Bar Scripti--!>
<script>
document.querySelector("form").addEventListener("submit", function(e) {
  e.preventDefault();

  var fileInput = document.querySelector("input[type='file']");
  if (!fileInput.files.length) return;

  var formData = new FormData(this);
  var xhr = new XMLHttpRequest();

  document.getElementById("progressContainer").style.display = "block";

  xhr.upload.addEventListener("progress", function(e) {
    if (e.lengthComputable) {
      var percentComplete = Math.round((e.loaded / e.total) * 100);
      var bar = document.getElementById("progressBar");
      bar.style.width = percentComplete + "%";
      bar.textContent = percentComplete + "%";
    }
  });

  // ✅ Yükleme bittikten sonra (PHP yanıt gönderdiğinde)
  xhr.addEventListener("load", function() {
    location.reload(); 
  });

  xhr.addEventListener("error", function() {
    document.getElementById("status").innerHTML = "❌ Yükleme hatası!";
  });

  xhr.open("POST", "upload.php", true);
  xhr.send(formData);
});
</script>



</body>
</html>
