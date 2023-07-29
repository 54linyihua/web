<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>上传文件</title>
    <style>
        body {
            background-color: #148FED;
            color: #fff;
            font-family: Arial, sans-serif;
        }

        form, table {
            margin: 20px;
            padding: 0;
        }

        input[type="file"], input[type="submit"] {
            margin: 10px 0;
            padding: 10px;
            border: none;
            background-color: #fff;
            color: #148FED;
            font-size: 16px;
            border-radius: 5px;
        }

        input[type="submit"]:hover {
            cursor: pointer;
            background-color: #148FED;
            color: #fff;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #fff;
        }

        th {
            background-color: #148FED;
        }

        .file-name {
            flex-grow: 1;
        }

        .download-btn {
            margin-left: 10px;
            padding: 5px 10px;
            border: none;
            background-color: #fff;
            color: #148FED;
            font-size: 14px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            cursor: pointer;
            background-color: #148FED;
            color: #fff;
        }

        .drag-area {
            border: 2px dashed #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .drag-area.highlight {
            border: 2px dashed #148FED;
        }

        .drag-text {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .drag-instruction {
            font-size: 14px;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <?php
    $allowed_extensions = array('zip', '7z', 'rar');
    $max_size = 100 * 1024 * 1024; // 100MB
    $max_total_size = 10024 * 1024 * 1024; // 1GB

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $upload_dir = 'uploads/';
        $total_size = get_folder_size($upload_dir);

        if (!empty($_FILES['file'])) {
            $file = $_FILES['file'];
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);

            if (in_array($file_extension, $allowed_extensions) && $file['size'] <= $max_size && $total_size + $file['size'] <= $max_total_size) {
                $timestamp = time();
                $upload_file = $upload_dir . date('Y.m.d.H.i.s', $timestamp) . '_' . $timestamp . '_' . basename($file['name']);

                if (move_uploaded_file($file['tmp_name'], $upload_file)) {
                    echo '<p>文件上传成功！</p>';

                    // 扫描并删除uploads文件夹下的PHP和html文件
                    $files = glob($upload_dir . '*.{php,html}', GLOB_BRACE);
                    foreach ($files as $file) {
                        unlink($file);
                    }
                } else {
                    echo '<p>文件上传失败！</p>';
                }
            } else {
                if ($total_size >= $max_total_size) {
                    echo '<p>上传失败，uploads文件夹已满！</p>';
                } else {
                    echo '<p>不支持上传该类型文件或文件大小超过限制！</p>';
                }
            }
        }
    }

    function get_folder_size($dir) {
        $size = 0;

        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $item) {
            if (is_file($item)) {
                $size += filesize($item);
            }

            if (is_dir($item)) {
                $size += get_folder_size($item);
            }
        }

        return $size;
    }
    ?>

    <form action="" method="post" enctype="multipart/form-data">
        <div class="drag-area" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
            <div class="drag-text">拖拽文件到这里上传</div>
            <div class="drag-instruction">或者点击此处选择文件</div>
            <input type="file" name="file" id="file-input" class="hidden" accept=".zip,.7z,.rar">
        </div>
        <br>
        <input type="submit" value="上传">
    </form>

    <?php
    $upload_dir = 'uploads/';
    $files = glob($upload_dir . '*');
    if (!empty($files)) {
        echo '<table>';
        echo '<tr><th>文件名</th><th>上传时间</th><th>文件大小</th><th>操作</th></tr>';
        foreach ($files as $file) {
            if (is_file($file)) {
                echo '<tr><td>' . basename($file) . '</td><td>' . date('Y-m-d H:i:s', filemtime($file)) . '</td><td>' . format_size(filesize($file)) . '</td><td><a class="download-btn" href="' . $file . '" download>下载</a></td></tr>';
            }
        }
        echo '</table>';
    } else {
        echo '<p>uploads文件夹中没有文件</p>';
    }

    function format_size($size) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
    ?>

    <script>
        function handleDragOver(event) {
            event.preventDefault();
            event.currentTarget.classList.add('highlight');
        }

        function handleDragLeave(event) {
            event.preventDefault();
            event.currentTarget.classList.remove('highlight');
        }

        function handleDrop(event) {
            event.preventDefault();
            event.currentTarget.classList.remove('highlight');
            document.getElementById('file-input').files = event.dataTransfer.files;
        }
    </script>
</body>
</html>