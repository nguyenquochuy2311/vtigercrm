/*!
 * TS Mobile UX – mobile layout for vtiger CRM 7.x (Dai Truong Son CRM)
 * Runs only on screens <= 767px wide; desktop is not touched.
 * - List screens: "Danh sach" panel, column search and sorting move into
 *   bottom sheets; records show as cards with a call button; an add button
 *   (FAB) and a bottom pager are added.
 * - Detail / edit screens: compact single-column fields, visible tabs.
 * Every action reuses vtiger's own buttons and links, so data handling,
 * permissions and search logic stay exactly as in vtiger.
 * Loaded through the combined JS bundle (vrender_combined_js() $tail in
 * includes/runtime/Viewer.php); the bundle key includes file mtimes, so
 * edits here are picked up automatically.
 */
(function (w, d) {
  'use strict';
  if (w.TSMobileUX) return;

  var VERSION = '1.0.0';
  var mq = w.matchMedia ? w.matchMedia('(max-width: 767px)') : { matches: false };
  var root = d.documentElement;
  var T = {
    lists: 'Danh sách', filter: 'Lọc', sort: 'Sắp xếp', sortBy: 'Sắp xếp theo', close: 'Đóng',
    add: 'Thêm mới', prev: 'Trước', next: 'Sau', call: 'Gọi', filterTitle: 'Lọc danh sách'
  };
  // Fields hidden on cards (still shown on the record page)
  var HIDE_ON_CARD = { modifiedtime: 1, modifiedby: 1 };
  var META_FIELDS = { createdtime: 1 };
  var STATUS_FIELDS = { leadstatus: 1, sales_stage: 1 };
  var MAX_CARD_FIELDS = 6;
  // Modules that get the new phone layout (Tiep thi + Ban hang). Others keep vtiger's layout.
  var MODULES = { Leads: 1, Potentials: 1, Contacts: 1, Accounts: 1, Campaigns: 1 };

  function $(s, c) { return (c || d).querySelector(s); }
  function $$(s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); }
  function text(e) { return e ? (e.textContent || '').replace(/\s+/g, ' ').trim() : ''; }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return '&#' + c.charCodeAt(0) + ';'; }); }
  function el(tag, cls, html) { var e = d.createElement(tag); if (cls) e.className = cls; if (html != null) e.innerHTML = html; return e; }
  function setText(e, t) { if (e && e.textContent !== t) e.textContent = t; }
  function isOn() { return !!mq.matches; }
  function closest(e, sel) { return e && e.closest ? e.closest(sel) : null; }

  /* ---------------- bottom sheets ---------------- */
  var backdrop;
  function closeBtn() {
    var b = el('button', 'tsm-sheet-x', '<i class="fa fa-times"></i>');
    b.type = 'button'; b.setAttribute('aria-label', T.close);
    return b;
  }
  function openSheet(name) {
    closeSheets();
    if (!backdrop || !d.body.contains(backdrop)) { backdrop = el('div', 'tsm-backdrop'); d.body.appendChild(backdrop); }
    root.classList.add('tsm-sheet-open', 'tsm-open-' + name);
  }
  function closeSheets() {
    ['tsm-sheet-open', 'tsm-open-lists', 'tsm-open-search', 'tsm-open-sort'].forEach(function (c) { root.classList.remove(c); });
  }

  /* ---------------- header ---------------- */
  function syncHeader() {
    var nav = $('nav.app-fixed-navbar') || $('.app-fixed-navbar');
    if (!nav) return;
    // the header grows while its menu or search box is open; keep the page where it is
    if ($('.in, .collapsing', nav)) return;
    var h = Math.round(nav.getBoundingClientRect().height);
    if (h > 0 && root.style.getPropertyValue('--tsm-hh') !== h + 'px') root.style.setProperty('--tsm-hh', h + 'px');
  }
  function syncAccent() {
    var a = $('.app-indicator-icon-container');
    var c = a && w.getComputedStyle(a).backgroundColor;
    if (c && c !== 'rgba(0, 0, 0, 0)' && c !== 'transparent' && root.style.getPropertyValue('--tsm-accent') !== c) {
      root.style.setProperty('--tsm-accent', c);
    }
  }

  /* ---------------- list view ---------------- */
  function currentModule() {
    var m = w._META || {};
    if (m.parent === 'Settings') return '';
    return m.module || '';
  }
  function isListView() { return !!$('#listViewContent table.listview-table'); }

  function headerLabels() {
    // vtiger's floatThead moves the real <thead> into a floating table
    var ths = $$('.floatThead-table tr.listViewContentHeader > th');
    if (!ths.length) ths = $$('#listview-table tr.listViewContentHeader > th');
    return ths.map(function (th) { return text(th); });
  }

  function addCall(td, val) {
    var num = (val || '').replace(/[^\d+]/g, '');
    if (num.length < 8 || $('.tsm-call', td)) return;
    var a = el('a', 'tsm-call', '<i class="fa fa-phone"></i>');
    a.href = 'tel:' + num; a.title = T.call; a.setAttribute('aria-label', T.call + ' ' + val);
    td.appendChild(a);
  }

  // Visible value of a list cell (vtiger keeps a hidden inline-edit copy in .edit)
  function cellValue(td) {
    var v = $('.value', td) || td;
    var s = typeof v.innerText === 'string' ? v.innerText : v.textContent;
    return (s || '').replace(/\s+/g, ' ').trim();
  }

  // Which fields a card shows first; the rest stay on the record page
  function fieldScore(name, type) {
    if (type === 'phone') return 95;
    if (STATUS_FIELDS[name]) return 90;
    if (type === 'currency') return 70;
    if (type === 'reference' || type === 'multireference') return 65;
    if (type === 'picklist' || type === 'multipicklist') return 60;
    if (type === 'date' || type === 'datetime') return 58;
    if (type === 'owner') return 50;
    if (type === 'email') return 45;
    return 40;
  }

  function enhanceRows(labels, table) {
    $$('tr.listViewEntries', table).forEach(function (tr) {
      if (tr.getAttribute('data-tsm') === '1') return;
      var id = tr.getAttribute('data-id');
      var cells = Array.prototype.slice.call(tr.children);
      var title = null, candidates = [];
      cells.forEach(function (td, i) {
        if (!td.classList.contains('listViewEntryValue')) return;
        var name = td.getAttribute('data-name') || '';
        var type = td.getAttribute('data-field-type') || '';
        var val = cellValue(td);
        // the template already renders data-tsm-label; only fill it in when missing (older templates)
        if (!td.getAttribute('data-tsm-label') && labels[i]) td.setAttribute('data-tsm-label', labels[i]);
        if (!val) { td.classList.add('tsm-empty'); return; }
        if (!title && id && $('a[href*="record=' + id + '"]', td)) { title = td; td.classList.add('tsm-title'); return; }
        if (HIDE_ON_CARD[name]) { td.classList.add('tsm-hide'); return; }
        if (META_FIELDS[name]) { td.classList.add('tsm-meta'); return; }
        if (type === 'text') { td.classList.add('tsm-text', 'tsm-wide'); return; }
        if (type === 'phone') { td.classList.add('tsm-phone'); addCall(td, val); }
        if (STATUS_FIELDS[name]) td.classList.add('tsm-status');
        candidates.push({ td: td, i: i, s: fieldScore(name, type) });
      });
      if (!title && candidates.length) { title = candidates.shift().td; title.classList.add('tsm-title'); }
      // Field order is now pure CSS (ts-mobile.css, by data-field-type) and the template already renders the
      // card classes/labels, so nothing is re-ordered or hidden here after the page has painted.
      tr.setAttribute('data-tsm', '1');
    });
  }

  function prepareSearchRow(labels) {
    var row = $('#listViewContent tr.searchRow');
    if (!row || row.getAttribute('data-tsm') === '1') return;
    Array.prototype.slice.call(row.children).forEach(function (th, i) {
      if (i === 0) { th.classList.add('tsm-search-actions'); return; }
      th.setAttribute('data-tsm-label', labels[i] || '');
      if (!$('input:not([type=hidden]), select', th)) th.classList.add('tsm-empty');
    });
    var head = el('th', 'tsm-sheet-head', esc(T.filterTitle));
    head.appendChild(closeBtn());
    row.insertBefore(head, row.firstChild);
    row.setAttribute('data-tsm', '1');
  }

  function prepareListsSheet() {
    var sb = $('#sidebar-essentials');
    if (!sb || sb.getAttribute('data-tsm') === '1') return;
    var bar = el('div', 'tsm-sheet-bar', '<span class="tsm-grab"></span>');
    bar.appendChild(closeBtn());
    sb.insertBefore(bar, sb.firstChild);
    sb.setAttribute('data-tsm', '1');
  }

  function activeSearchCount() {
    var n = 0;
    $$('#listViewContent tr.searchRow .listSearchContributor').forEach(function (x) {
      if (x.tagName === 'SELECT') {
        var picked = Array.prototype.filter.call(x.selectedOptions || [], function (o) { return o.value !== ''; });
        if (picked.length && (x.multiple || x.selectedIndex > 0)) n++;
      }
      else if (x.tagName === 'INPUT' && (x.value || '').trim()) n++;
    });
    return n;
  }

  var toolbar;
  function ensureToolbar() {
    var content = $('#listViewContent');
    if (!content) return;
    // ListViewPreProcess.tpl renders the toolbar shell on the server (so the page lays out right on first paint);
    // build it only for pages that do not have it.
    if (!toolbar || !d.body.contains(toolbar)) toolbar = $('.tsm-toolbar');
    if (!toolbar) {
      toolbar = el('div', 'tsm-toolbar');
      toolbar.innerHTML =
        '<button type="button" class="tsm-chip" data-tsm-open="lists"><i class="fa fa-list-ul"></i><span class="tsm-chip-text"></span><i class="fa fa-angle-down"></i></button>' +
        '<button type="button" class="tsm-tbtn tsm-sort-btn" data-tsm-open="sort" title="' + esc(T.sort) + '" aria-label="' + esc(T.sort) + '"><i class="fa fa-sort-amount-asc"></i></button>' +
        '<button type="button" class="tsm-tbtn tsm-filter-btn" data-tsm-open="search"><i class="fa fa-filter"></i><span>' + esc(T.filter) + '</span><span class="tsm-badge" hidden></span></button>';
      content.parentNode.insertBefore(toolbar, content);
    }
    var act = $('#sidebar-essentials li.listViewFilter.active a.filterName');
    var name = act ? (act.getAttribute('title') || text(act)) : text($('.module-breadcrumb .current-filter-name'));
    setText($('.tsm-chip-text', toolbar), name || T.lists);
    var n = activeSearchCount();
    var badge = $('.tsm-badge', toolbar);
    setText(badge, n ? String(n) : '');
    badge.hidden = !n;
    var fb = $('.tsm-filter-btn', toolbar);
    fb.classList.toggle('tsm-active', n > 0);
    fb.hidden = !$('#listViewContent tr.searchRow');
    $('.tsm-sort-btn', toolbar).hidden = !$('#listViewContent a.listViewContentHeaderValues');
    $('.tsm-chip', toolbar).hidden = !$('#sidebar-essentials');
  }

  var fab;
  function ensureFab() {
    var add = $('[id$="_listView_basicAction_LBL_ADD_RECORD"]');
    if (!fab || !d.body.contains(fab)) {
      fab = el('button', 'tsm-fab', '<i class="fa fa-plus"></i>');
      fab.type = 'button'; fab.setAttribute('aria-label', T.add);
      fab.addEventListener('click', function () {
        var b = $('[id$="_listView_basicAction_LBL_ADD_RECORD"]');
        if (b) b.click();
      });
      d.body.appendChild(fab);
    }
    fab.hidden = !add;
    if (add) fab.title = text(add) || T.add;
  }

  function pageText() {
    // "1 to 20" / "1 to 20 of 356" (vtiger writes the total here once it is counted)
    var range = text($('#listViewContent .pageNumbersText')).replace(/\s+(to|đến)\s+/i, '–').replace(/\s+(of|của)\s+/i, ' / ');
    var totalEl = $('#listViewContent .totalNumberOfRecords');
    var total = '';
    if (totalEl && !totalEl.classList.contains('hide')) {
      total = text(totalEl).replace(/^(of|của)\s*/i, '');
      if (!total && $('.showTotalCountIcon', totalEl)) total = '?';   // tap to count all records
    }
    return { range: range, total: total };
  }
  function clickReal(sel) {
    var x = $('#listViewContent ' + sel);
    if (x && !x.disabled) { x.click(); w.scrollTo(0, 0); }
  }
  function ensurePager() {
    var anchor = $('#listViewContent .floatThead-wrapper') || $('#listViewContent #table-content');
    if (!anchor) return;
    var prev = $('#listViewContent #PreviousPageButton'), next = $('#listViewContent #NextPageButton');
    var pager = $('#listViewContent .tsm-pager');
    if (!prev && !next) { if (pager) pager.parentNode.removeChild(pager); return; }
    if (!pager) {
      pager = el('div', 'tsm-pager',
        '<button type="button" class="tsm-prev"><i class="fa fa-angle-left"></i> ' + esc(T.prev) + '</button>' +
        '<span class="tsm-range"><span class="tsm-range-a"></span><span class="tsm-range-sep"> / </span><span class="tsm-total"></span></span>' +
        '<button type="button" class="tsm-next">' + esc(T.next) + ' <i class="fa fa-angle-right"></i></button>');
      anchor.parentNode.insertBefore(pager, anchor.nextSibling);
      pager.addEventListener('click', function (e) {
        if (closest(e.target, '.tsm-prev')) clickReal('#PreviousPageButton');
        else if (closest(e.target, '.tsm-next')) clickReal('#NextPageButton');
        else if (closest(e.target, '.tsm-total')) { var t = $('#listViewContent .totalNumberOfRecords'); if (t) t.click(); }
      });
    }
    $('.tsm-prev', pager).disabled = !prev || prev.disabled;
    $('.tsm-next', pager).disabled = !next || next.disabled;
    var p = pageText();
    setText($('.tsm-range-a', pager), p.range);
    setText($('.tsm-total', pager), p.total);
    $('.tsm-range-sep', pager).style.display = p.total ? '' : 'none';
    pager.style.display = (!p.range && prev && prev.disabled && next && next.disabled) ? 'none' : '';
  }

  var sortSheet;
  function openSort() {
    var links = $$('#listViewContent a.listViewContentHeaderValues');
    var seen = {};
    links = links.filter(function (a) { var c = a.getAttribute('data-columnname'); if (!c || seen[c]) return false; seen[c] = 1; return true; });
    if (!sortSheet || !d.body.contains(sortSheet)) {
      sortSheet = el('div', 'tsm-sheet tsm-sort-sheet');
      d.body.appendChild(sortSheet);
      sortSheet.addEventListener('click', function (e) {
        var b = closest(e.target, '[data-col]');
        if (!b) return;
        var col = b.getAttribute('data-col');
        var target = $$('#listViewContent a.listViewContentHeaderValues').filter(function (a) { return a.getAttribute('data-columnname') === col; })[0];
        closeSheets();
        if (target) target.click();
      });
    }
    var html = '<div class="tsm-sheet-head">' + esc(T.sortBy) + '</div>';
    links.forEach(function (a) {
      var icon = $('i', a);
      var cls = icon ? icon.className : '';
      var dir = /fa-sort-(asc|up)|fa-sort-alpha-asc|fa-sort-amount-asc/.test(cls) ? 'up' : /fa-sort-(desc|down)|fa-sort-alpha-desc|fa-sort-amount-desc/.test(cls) ? 'down' : '';
      html += '<button type="button" class="tsm-sort-item' + (dir ? ' tsm-on' : '') + '" data-col="' + esc(a.getAttribute('data-columnname')) + '">' +
        '<span>' + esc(text(a)) + '</span>' + (dir ? '<i class="fa fa-long-arrow-' + dir + '"></i>' : '') + '</button>';
    });
    sortSheet.innerHTML = html;
    $('.tsm-sheet-head', sortSheet).appendChild(closeBtn());
    openSheet('sort');
  }

  /* vtiger puts perfect-scrollbar on the list container. On a phone the list is a plain column of cards that the page itself scrolls, so
     the plugin only gets in the way: its touch handler cancels native scrolling (preventDefault) whenever the container's scroll height
     differs from its height by even a pixel, and it measures layout on every touch event. Release it on phones; if the desktop layout
     comes back (rotating to landscape) reload the page so vtiger builds it again. */
  var listScrollerReleased = false;
  function releaseListScroller() {
    var $j = w.jQuery;
    if (!$j || !$j.fn || !$j.fn.perfectScrollbar) return;
    try {
      var box = $j('#table-content.ps-container');
      if (box.length) { box.perfectScrollbar('destroy'); listScrollerReleased = true; }
      // NOTE: do not floatThead('destroy') here — with vtiger's setup the plugin drops the real <thead>
      // (header row + search row + sort links vanish). Its scroll/resize handlers cost < 1 ms, so it stays.
    } catch (err) { /* keep vtiger working */ }
  }

  /* vtiger's own window "resize" handler destroys and rebuilds the custom scrollbar of the app menu every time it fires (7 ms on a fast
     laptop, 50+ ms on a mid-range phone). Phone browsers fire "resize" over and over while the address bar slides in and out during a
     scroll, so the page stutters. Make that handler run only when the width really changed. */
  function guardVtigerResize() {
    var $j = w.jQuery;
    if (!$j || !$j._data) return;
    var list;
    try { list = ($j._data(w, 'events') || {}).resize || []; } catch (err) { return; }
    list.forEach(function (h) {
      if (h.tsmGuarded || typeof h.handler !== 'function') return;
      var src = '';
      try { src = Function.prototype.toString.call(h.handler); } catch (err) { return; }
      if (src.indexOf('app-modules-dropdown') < 0) return;
      var orig = h.handler, seen = w.innerWidth;
      h.handler = function () {
        if (isOn() && w.innerWidth === seen) return;
        seen = w.innerWidth;
        return orig.apply(this, arguments);
      };
      h.tsmGuarded = true;
    });
  }

  var lastTable = null;
  function enhanceList() {
    var table = $('#listViewContent table#listview-table') || $('#listViewContent table.listview-table:not(.floatThead-table)');
    if (!table) return;
    if (table !== lastTable) { lastTable = table; closeSheets(); }  // list reloaded (filter, search, page)
    var labels = headerLabels();
    $('#listViewContent').classList.add('tsm-cards-on');
    table.classList.add('tsm-cards');
    enhanceRows(labels, table);
    prepareSearchRow(labels);
    prepareListsSheet();
    ensureToolbar();
    ensureFab();
    ensurePager();
    releaseListScroller();
  }

  /* ---------------- detail view ---------------- */
  function enhanceDetail() {
    // call button next to phone numbers on the record page
    // ("Chi tiet" tab marks the value span; the summary tab only has a hidden input with data-type)
    var values = $$('.detailViewContainer span.value[data-field-type="phone"]');
    $$('.detailViewContainer input.fieldBasicData[data-type="phone"]').forEach(function (inp) {
      var v = $('span.value', closest(inp, 'td.fieldValue') || inp.parentNode);
      if (v && values.indexOf(v) < 0) values.push(v);
    });
    values.forEach(function (v) {
      var num = text(v).replace(/[^\d+]/g, '');
      var a = v.nextSibling && v.nextSibling.classList && v.nextSibling.classList.contains('tsm-call-inline') ? v.nextSibling : null;
      if (num.length < 8) { if (a) a.parentNode.removeChild(a); return; }
      if (!a) {
        a = el('a', 'tsm-call-inline', '<i class="fa fa-phone"></i> ' + esc(T.call));
        v.parentNode.insertBefore(a, v.nextSibling);
      }
      if (a.getAttribute('href') !== 'tel:' + num) a.setAttribute('href', 'tel:' + num);
    });
  }

  // Related-record tabs show only 2-letter icons on phones; add their names
  function enhanceTabs() {
    var tabs = $$('.related-tabs li.tab-item');
    if (!tabs.length) return;
    tabs.forEach(function (li) {
      if (li.getAttribute('data-tsm') === '1') return;
      var a = $('a', li);
      if (!a || $('.tab-label', li)) { li.setAttribute('data-tsm', '1'); return; }
      var label = li.getAttribute('oldtitle') || li.getAttribute('title') || a.getAttribute('title') || '';
      if (!label) return;   // qtip has not moved the title yet; try again on the next pass
      var s = el('span', 'tsm-tab-text');
      s.textContent = label;
      var icon = $('.tab-icon', a);
      a.insertBefore(s, icon ? icon.nextSibling : a.firstChild);
      li.setAttribute('data-tsm', '1');
    });
    var ul = $('.related-tabs .nav-tabs'), act = $('.related-tabs li.tab-item.active');
    if (ul && act && ul.getAttribute('data-tsm-scrolled') !== act.getAttribute('data-label-key')) {
      ul.setAttribute('data-tsm-scrolled', act.getAttribute('data-label-key') || '');
      ul.scrollLeft = Math.max(0, act.offsetLeft - 12);
    }
  }

  /* ---------------- main loop ---------------- */
  function enhance() {
    var on = isOn();
    guardVtigerResize();
    root.classList.toggle('tsm', on);
    if (!on) { closeSheets(); root.classList.remove('tsm-list', 'tsm-mod'); if (listScrollerReleased) { listScrollerReleased = false; w.location.reload(); } return; }
    syncHeader();
    var mod = !!MODULES[currentModule()];
    root.classList.toggle('tsm-mod', mod);
    if (!mod) { root.classList.remove('tsm-list'); return; }
    syncAccent();
    var list = isListView();
    root.classList.toggle('tsm-list', list);
    if (list) enhanceList();
    enhanceDetail();
    enhanceTabs();
  }

  var queued = false;
  function schedule() {
    if (queued) return;
    queued = true;
    // a short timer (not requestAnimationFrame, which pauses in background tabs) batches DOM changes
    w.setTimeout(function () { queued = false; try { enhance(); } catch (err) { if (w.console) console.warn('TSMobileUX', err); } }, 40);
  }

  // open / close sheets
  d.addEventListener('click', function (e) {
    if (!isOn()) return;
    var t = e.target;
    var opener = closest(t, '[data-tsm-open]');
    if (opener) {
      e.preventDefault();
      var n = opener.getAttribute('data-tsm-open');
      if (n === 'sort') openSort(); else openSheet(n);
      return;
    }
    if (closest(t, '.tsm-sheet-x') || (t.classList && t.classList.contains('tsm-backdrop'))) { e.preventDefault(); closeSheets(); return; }
    if (closest(t, '#sidebar-essentials a.filterName, #sidebar-essentials .tagLabel, #sidebar-essentials #createFilter')) { w.setTimeout(closeSheets, 60); }
    if (closest(t, 'tr.searchRow [data-trigger="listSearch"], tr.searchRow [data-trigger="clearListSearch"]')) { w.setTimeout(closeSheets, 60); }
  });
  d.addEventListener('keydown', function (e) { if (e.key === 'Escape' || e.keyCode === 27) closeSheets(); });
  d.addEventListener('click', function (e) {
    var t = e.target;
    // Call button: dial only, do not open the record
    if (closest(t, 'a.tsm-call')) { e.stopPropagation(); return; }
    // vtiger opens the record when a cell is tapped; make the card's padding and gaps work too
    if (isOn() && t.matches && t.matches('table.tsm-cards tr.listViewEntries') && t.getAttribute('data-recordurl') &&
        !String(w.getSelection ? w.getSelection() : '')) {
      w.location.href = t.getAttribute('data-recordurl');
    }
  }, true);
  // Double tap on a card would start vtiger's inline edit, which has no save button on cards
  d.addEventListener('dblclick', function (e) { if (isOn() && closest(e.target, 'table.tsm-cards tr.listViewEntries')) e.stopPropagation(); }, true);
  // Search fields: Enter runs the search and closes the sheet
  d.addEventListener('keydown', function (e) {
    // (not inside select2 boxes, where Enter picks an option)
    if ((e.key === 'Enter' || e.keyCode === 13) && closest(e.target, 'tr.searchRow input.listSearchContributor')) w.setTimeout(closeSheets, 60);
  });

  if (mq.addEventListener) mq.addEventListener('change', schedule); else if (mq.addListener) mq.addListener(schedule);
  // Phone browsers fire "resize" over and over while the address bar slides in and out during a scroll. enhance() measures layout and
  // walks the list, so running it for those events makes scrolling stutter. Only a change of width (rotation, split screen) matters.
  var lastWidth = w.innerWidth;
  w.addEventListener('resize', function () {
    if (w.innerWidth === lastWidth) return;
    lastWidth = w.innerWidth;
    schedule();
  });
  w.addEventListener('orientationchange', schedule);

  function start() {
    enhance();
    if (w.MutationObserver) {
      new MutationObserver(function (list) {
        for (var i = 0; i < list.length; i++) {
          if (!closest(list[i].target, '.tsm-toolbar, .tsm-pager, .tsm-sheet, .tsm-fab')) { schedule(); return; }
        }
      }).observe(d.body, { childList: true, subtree: true });
    }
  }

  // Add the class as early as possible to avoid a flash of the old layout
  root.classList.toggle('tsm', isOn());
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', start); else start();

  w.TSMobileUX = { version: VERSION, refresh: schedule, close: closeSheets };
})(window, document);
