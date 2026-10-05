/*!
 * TS list count – tự hiện tổng số bản ghi ("1 to 20 of 9250") và tổng số trang trên danh sách.
 * vtiger mặc định chỉ hiện "1 to 20 of ?" và chờ người dùng bấm dấu "?" (để tiết kiệm 1 câu COUNT). Với dữ liệu hiện tại
 * (~10 nghìn khách) câu COUNT rất nhẹ, còn thiếu tổng số làm nhân viên tưởng danh sách chỉ có 20 khách / bị thiếu khách.
 * File này chỉ "bấm giúp" dấu "?" một lần cho mỗi lần danh sách tải xong; toàn bộ việc đếm/hiển thị vẫn do vtiger làm
 * (Vtiger_List_Js.totalNumOfRecords). Chỉ áp dụng cho danh sách chính (#listViewContent), không đụng popup/related list.
 * Nạp qua gói JS chung (vrender_combined_js() $tail trong includes/runtime/Viewer.php).
 */
(function (w, d) {
  'use strict';
  if (w.TSListCount) return;
  var timer = null;

  // Khi vtiger đã hiện tổng ("1 to 20 of 9250"), thêm số trang ngay cạnh: "· trang 1/463", và điền sẵn "Page x of y"
  // trong menu nhảy trang (vtiger chỉ điền khi mở menu đó).
  function addPages(box, total) {
    var txt = box.querySelector('.pageNumbersText');
    if (!txt || txt.querySelector('.tsc-pages')) return;
    var limit = parseInt((box.querySelector('#pageLimit') || {}).value || '20', 10) || 20;
    var page = parseInt((box.querySelector('#pageNumber') || {}).value || '1', 10) || 1;
    var pages = Math.max(1, Math.ceil(total / limit));
    var tp = box.querySelector('#totalPageCount');
    if (tp && !tp.textContent.trim()) tp.textContent = String(pages);
    var s = d.createElement('span');
    s.className = 'tsc-pages';
    s.textContent = ' \u00b7 trang ' + page + '/' + pages;
    txt.appendChild(s);
  }

  function check() {
    var box = d.querySelector('#listViewContent');
    var el = box && box.querySelector('.totalNumberOfRecords');
    if (!el) return;                                                    // chưa có thanh trang
    var total = parseInt((box.querySelector('#totalCount') || {}).value || '', 10);
    if (el.classList.contains('hide')) {                                // vtiger đã hiện tổng, hoặc danh sách trống
      if (total > 0) addPages(box, total);
      return;
    }
    var icon = el.querySelector('.showTotalCountIcon');
    if (!icon || icon.classList.contains('hide')) return;              // đã bấm rồi
    var n = parseInt(el.getAttribute('data-tsc-n') || '0', 10);
    if (n >= 3 || el.getAttribute('data-tsc-pending') === '1') return; // tối đa 3 lần/lượt hiển thị, không bấm chồng
    el.setAttribute('data-tsc-n', String(n + 1));
    el.setAttribute('data-tsc-pending', '1');
    el.click();                                                         // handler của vtiger gọi getPageCount rồi điền "of N"
    w.setTimeout(function () { el.removeAttribute('data-tsc-pending'); schedule(); }, 1500);
  }

  function schedule() {
    if (timer) return;
    timer = w.setTimeout(function () { timer = null; try { check(); } catch (err) { /* không làm hỏng trang */ } }, 250);
  }

  function start() {
    schedule();
    if (w.MutationObserver) {
      new MutationObserver(function (list) {
        for (var i = 0; i < list.length; i++) {
          var t = list[i].target;
          if (t && t.closest && t.closest('#listViewContent')) { schedule(); return; }
        }
      }).observe(d.body, { childList: true, subtree: true });
    }
  }

  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', start); else start();
  w.TSListCount = { refresh: schedule };
})(window, document);
