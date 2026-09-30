<?php
/**
 * Router cho PHP built-in server (php -S) — tang toc phuc vu file tinh.
 * - Them Cache-Control (versioned ?v= -> cache 1 nam) + Last-Modified + ETag
 * - Tra 304 khi browser da co cache
 * - gzip cho file text (js/css/svg/json) -> giam 70-80% byte lan dau
 * - Request dong (index.php, khong phai file tinh) -> chuyen cho vtiger nhu cu
 * Dat trong docroot new_crm/. Chay: php -S 0.0.0.0:8091 -t docroot _static_router.php
 */

$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriPath = rawurldecode($uriPath);
$docroot = __DIR__;
$target  = $docroot . $uriPath;

// Chan path traversal: file phai nam trong docroot va la file that
$real = realpath($target);
$rootReal = realpath($docroot);
$isFile = $real && $rootReal && strpos($real, $rootReal) === 0 && is_file($real);

$STATIC = array(
    'js'   => 'application/javascript', 'css' => 'text/css',
    'png'  => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
    'webp' => 'image/webp',
    'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf',
    'eot'  => 'application/vnd.ms-fontobject', 'otf' => 'font/otf',
    'map'  => 'application/json', 'json' => 'application/json',
);
$GZIP = array('js','css','svg','json','map');

$ext = $isFile ? strtolower(pathinfo($real, PATHINFO_EXTENSION)) : '';

// Chi tu phuc vu cac loai file tinh trong danh sach; con lai (.php...) tra ve
// false de built-in server xu ly (index.php van chay vtiger binh thuong).
if ($isFile && isset($STATIC[$ext])) {
    $mtime = filemtime($real);
    $size  = filesize($real);
    $etag  = '"' . $mtime . '-' . $size . '"';

    // asset co ?v= (versioned) -> immutable, cache 1 nam; con lai cache 1 ngay
    $versioned = isset($_GET['v']) || strpos($_SERVER['REQUEST_URI'], '?v=') !== false;
    $maxAge = $versioned ? 31536000 : 86400;

    header('Cache-Control: public, max-age=' . $maxAge);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
    header('ETag: ' . $etag);
    header('Content-Type: ' . $STATIC[$ext]);

    // 304 neu browser da co ban moi nhat
    $inm = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : '';
    $ims = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : 0;
    if ($inm === $etag || ($ims && $ims >= $mtime)) {
        header('HTTP/1.1 304 Not Modified');
        return true;
    }

    $data = file_get_contents($real);
    $ae = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
    if (in_array($ext, $GZIP) && strpos($ae, 'gzip') !== false && function_exists('gzencode')) {
        $data = gzencode($data, 6);
        header('Content-Encoding: gzip');
        header('Vary: Accept-Encoding');
    }
    header('Content-Length: ' . strlen($data));
    echo $data;
    return true;
}

// Khong phai file tinh -> de built-in server chay (index.php -> vtiger)
if ($isFile) {
    return false; // .php hoac file khac trong docroot: server tu xu ly
}

// URL khong tro file that (vd '/', route dep) -> vao index.php cua vtiger
require $docroot . '/index.php';
