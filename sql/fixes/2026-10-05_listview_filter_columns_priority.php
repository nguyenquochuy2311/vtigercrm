<?php
// cv_reorder.php <config.inc.php> <dry|GO|restore> <admin|all> <backup.tsv> [Module=Leads] [priorityCsv]
// Đặt các cột ưu tiên lên đầu mọi bộ lọc (vtiger_customview/vtiger_cvcolumnlist) của một module. Mặc định Leads:
//   createdtime (Thời gian tạo), lastname (Họ/tên khách), phone (Điện thoại), assigned_user_id (Người được giao), cf_771 (Dự Án)
//   Contacts: createdtime,lastname,phone,mobile,assigned_user_id,cf_775   Potentials: createdtime,contact_id,potentialname,assigned_user_id,cf_769
// Chuỗi cột chuẩn dựng từ vtiger_field theo đúng quy tắc của vtiger: table:column:fieldname:Module_Nhãn_Field:Kiểu.
// Các cột còn lại giữ nguyên thứ tự phía sau; cột thiếu thì thêm vào. scope: admin = chỉ bộ lọc của admin (userid=1), all = tất cả.
// dry: chỉ in kế hoạch. GO: backup TSV rồi ghi trong 1 transaction. restore <tsv>: nạp lại đúng các dòng trong backup.
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
list(, $cfg, $mode, $scope, $bak, $MODULE, $PRI) = $argv + array(null, null, 'dry', 'admin', null, 'Leads', 'createdtime,lastname,phone,assigned_user_id,cf_771');
require $cfg;
$m = mysqli_connect($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name'], (int)ltrim($dbconfig['db_port'], ':') ?: 3306);
if (!$m) { fwrite(STDERR, "DB CONNECT FAIL\n"); exit(2); }
mysqli_set_charset($m, 'utf8');
function q($m, $sql) { $r = mysqli_query($m, $sql); if ($r === false) { @mysqli_query($m, 'ROLLBACK'); fwrite(STDERR, "SQL ERR: " . mysqli_error($m) . "\n$sql\n"); exit(3); } return $r; }
function fld($cn) { $p = explode(':', $cn); return isset($p[2]) ? $p[2] : $cn; }
$PRIORITY = array_values(array_filter(explode(',', $PRI)));
$MODULE = preg_replace('/[^A-Za-z]/', '', $MODULE);

if ($mode === 'restore') {
    $rows = array(); $cvs = array(); $f = fopen($bak, 'r'); fgets($f);
    while (($l = fgets($f)) !== false) { $p = explode("\t", rtrim($l, "\n"), 3); if (count($p) < 3) continue; $rows[] = $p; $cvs[$p[0]] = 1; }
    if (!$cvs) { echo "backup rỗng\n"; exit(1); }
    q($m, 'START TRANSACTION'); q($m, 'DELETE FROM vtiger_cvcolumnlist WHERE cvid IN (' . implode(',', array_map('intval', array_keys($cvs))) . ')');
    foreach ($rows as $p) q($m, 'INSERT INTO vtiger_cvcolumnlist (cvid, columnindex, columnname) VALUES (' . (int)$p[0] . ',' . (int)$p[1] . ",'" . mysqli_real_escape_string($m, $p[2]) . "')");
    q($m, 'COMMIT'); echo "ĐÃ KHÔI PHỤC " . count($cvs) . " bộ lọc, " . count($rows) . " dòng cột\n"; exit(0);
}

// chuỗi cột chuẩn dựng từ vtiger_field (quy tắc đã kiểm chứng trùng khít các chuỗi có sẵn trong vtiger_cvcolumnlist)
$canon = array();
$r = q($m, "SELECT f.fieldname, CONCAT(f.tablename, ':', f.columnname, ':', f.fieldname, ':', t.name, '_', REPLACE(f.fieldlabel, ' ', '_'), ':', SUBSTRING_INDEX(f.typeofdata, '~', 1)) AS cn FROM vtiger_field f JOIN vtiger_tab t ON t.tabid=f.tabid WHERE t.name='" . mysqli_real_escape_string($m, $MODULE) . "' AND f.presence IN (0,2)");
while ($x = mysqli_fetch_row($r)) if (in_array($x[0], $PRIORITY)) $canon[$x[0]] = $x[1];
foreach ($PRIORITY as $p) if (empty($canon[$p])) { fwrite(STDERR, "thiếu field $p ở module $MODULE\n"); exit(4); }
// đối chiếu với chuỗi đã có trong các bộ lọc: nếu có thì phải trùng
$r = q($m, "SELECT DISTINCT cl.columnname FROM vtiger_cvcolumnlist cl JOIN vtiger_customview cv ON cv.cvid=cl.cvid WHERE cv.entitytype='" . mysqli_real_escape_string($m, $MODULE) . "'");
while ($x = mysqli_fetch_row($r)) { $f = fld($x[0]); if (isset($canon[$f]) && $canon[$f] !== $x[0]) { fwrite(STDERR, "CHUỖI LỆCH $f: có sẵn '{$x[0]}' vs dựng '{$canon[$f]}'\n"); exit(7); } }
foreach ($PRIORITY as $p) echo "  chuẩn $p => {$canon[$p]}\n";

$where = $scope === 'all' ? '1=1' : 'userid=1';
$views = array(); $r = q($m, "SELECT cvid, viewname, userid FROM vtiger_customview WHERE entitytype='" . mysqli_real_escape_string($m, $MODULE) . "' AND $where ORDER BY cvid");
while ($x = mysqli_fetch_assoc($r)) $views[] = $x;
$plan = array(); $backup = array("cvid\tcolumnindex\tcolumnname\n");
foreach ($views as $v) {
    $cols = array(); $r = q($m, "SELECT columnindex, columnname FROM vtiger_cvcolumnlist WHERE cvid=" . (int)$v['cvid'] . " ORDER BY columnindex");
    while ($x = mysqli_fetch_row($r)) { $cols[] = $x[1]; $backup[] = $v['cvid'] . "\t" . $x[0] . "\t" . $x[1] . "\n"; }
    $rest = array(); $seen = array();
    foreach ($cols as $c) { $f = fld($c); if (in_array($f, $PRIORITY) || isset($seen[$f])) continue; $seen[$f] = 1; $rest[] = $c; }
    $new = array(); foreach ($PRIORITY as $p) $new[] = $canon[$p]; $new = array_merge($new, $rest);
    $oldF = array_map('fld', $cols); $newF = array_map('fld', $new);
    if ($oldF === $newF) { printf("cv%-3d %-34s (uid %s) giữ nguyên\n", $v['cvid'], mb_substr($v['viewname'], 0, 34), $v['userid']); continue; }
    $plan[] = array($v, $new);
    printf("cv%-3d %-34s (uid %s)\n      cũ: %s\n      mới: %s\n", $v['cvid'], mb_substr($v['viewname'], 0, 34), $v['userid'], implode(',', $oldF), implode(',', $newF));
}
echo "\n" . count($plan) . "/" . count($views) . " bộ lọc sẽ đổi (scope=$scope)\n";
if ($mode !== 'GO') { echo "(dry-run, chưa ghi)\n"; exit(0); }
if (!$plan) { echo "không có gì để đổi\n"; exit(0); }
if (!$bak) { fwrite(STDERR, "thiếu đường dẫn backup\n"); exit(5); }
file_put_contents($bak, implode('', $backup)); chmod($bak, 0600); echo "backup: $bak (" . (count($backup) - 1) . " dòng)\n";
q($m, 'START TRANSACTION');
foreach ($plan as $pl) { list($v, $new) = $pl; $id = (int)$v['cvid'];
    q($m, "DELETE FROM vtiger_cvcolumnlist WHERE cvid=$id");
    foreach ($new as $i => $cn) q($m, "INSERT INTO vtiger_cvcolumnlist (cvid, columnindex, columnname) VALUES ($id, $i, '" . mysqli_real_escape_string($m, $cn) . "')");
}
// kiểm chứng trong transaction
$bad = 0; foreach ($plan as $pl) { list($v, $new) = $pl; $r = q($m, "SELECT COUNT(*), COUNT(DISTINCT columnindex), MIN(columnindex), MAX(columnindex) FROM vtiger_cvcolumnlist WHERE cvid=" . (int)$v['cvid']); $x = mysqli_fetch_row($r); if ($x[0] != count($new) || $x[1] != count($new) || $x[2] != 0 || $x[3] != count($new) - 1) { $bad++; echo "LỆCH cv{$v['cvid']}\n"; } }
if ($bad) { q($m, 'ROLLBACK'); echo "ROLLBACK ($bad lệch)\n"; exit(6); }
q($m, 'COMMIT'); echo "ĐÃ GHI " . count($plan) . " bộ lọc.\n";
