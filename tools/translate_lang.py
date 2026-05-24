#!/usr/bin/env python3
"""Auto-translate Vtiger language files from en_us to vi_vn.

Backends (auto-detected):
  1. Claude (Anthropic) — needs ANTHROPIC_API_KEY + `pip install anthropic`
  2. Google Translate (free) — needs `pip install deep-translator`

Usage:
  python3 tools/translate_lang.py --all
  python3 tools/translate_lang.py --file Contacts.php
  python3 tools/translate_lang.py --all --dry-run
  python3 tools/translate_lang.py --all --force        # overwrite existing
  python3 tools/translate_lang.py --all --backend google
"""

from __future__ import annotations
import argparse
import json
import os
import re
import sys
import time
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SRC_DIR = ROOT / "languages" / "en_us"
DST_DIR = ROOT / "languages" / "vi_vn"

# Match:   'KEY' => 'VALUE',   or   "KEY" => "VALUE",
# Capture indent, key-with-quotes, arrow+spaces, opening quote, value, closing-quote, trailing
LINE_RE = re.compile(
    r"""^(?P<indent>[ \t]*)
        (?P<keyq>['"])(?P<key>(?:\\.|(?!(?P=keyq)).)*)(?P=keyq)
        (?P<arrow>\s*=>\s*)
        (?P<valq>['"])(?P<val>(?:\\.|(?!(?P=valq)).)*)(?P=valq)
        (?P<tail>\s*,?\s*(?://.*)?)$""",
    re.VERBOSE,
)

# Skip values that are pure code/tokens/format strings — never translate
SKIP_VALUE_RE = re.compile(r"^[\s\-\.\,/\\:_=>%\d#@*]+$")

# Skip if value contains only placeholders/HTML and no real words
def should_translate(value: str) -> bool:
    if not value.strip():
        return False
    if SKIP_VALUE_RE.match(value):
        return False
    # If value is just %s or %1$s etc.
    stripped = re.sub(r"%(?:\d+\$)?[sdif]|<[^>]+>|\\[ntr]|\s+", "", value)
    if not stripped:
        return False
    return True


def parse_php(path: Path) -> tuple[list[str], list[dict]]:
    """Return (lines, entries) where entries point to translatable lines."""
    text = path.read_text(encoding="utf-8")
    lines = text.splitlines(keepends=True)
    entries = []
    for i, line in enumerate(lines):
        m = LINE_RE.match(line.rstrip("\r\n"))
        if not m:
            continue
        val = m.group("val")
        # Unescape \' and \" so we send clean English to the translator
        val_unescaped = val.replace("\\'", "'").replace('\\"', '"')
        if not should_translate(val_unescaped):
            continue
        entries.append({
            "line_idx": i,
            "match": m,
            "key": m.group("key"),
            "val": val_unescaped,
        })
    return lines, entries


def reassemble(lines: list[str], entries: list[dict], translations: dict[int, str]) -> str:
    """Rewrite lines with translated values."""
    out = list(lines)
    for e in entries:
        if e["line_idx"] not in translations:
            continue
        vi = translations[e["line_idx"]]
        m = e["match"]
        # Always emit double-quoted with proper escaping (handles Vietnamese apostrophes safely)
        vi_escaped = vi.replace("\\", "\\\\").replace('"', '\\"')
        new_line = (
            m.group("indent")
            + m.group("keyq") + m.group("key") + m.group("keyq")
            + m.group("arrow")
            + '"' + vi_escaped + '"'
            + m.group("tail")
        )
        # Preserve newline ending of original
        orig = lines[e["line_idx"]]
        nl = ""
        if orig.endswith("\r\n"):
            nl = "\r\n"
        elif orig.endswith("\n"):
            nl = "\n"
        out[e["line_idx"]] = new_line + nl
    return "".join(out)


# ---------- Backends ---------- #

SYSTEM_PROMPT = """You translate English UI strings from Vtiger CRM into Vietnamese.

Rules:
- Output JSON only — an array of strings in the SAME order as input.
- Preserve placeholders EXACTLY: %s, %1$s, %d, %02d, {0}, {name}, etc.
- Preserve HTML tags exactly: <b>, <a href="...">, <br>, etc.
- Preserve special escape sequences: \\n, \\t, \\'
- Translate CRM domain terms naturally:
  Lead → Khách hàng tiềm năng (or "Lead" if too long)
  Opportunity / Potential → Cơ hội
  Account / Organization → Tổ chức
  Contact → Liên hệ
  Quote → Báo giá
  Invoice → Hoá đơn
  Sales Order → Đơn bán hàng
  Purchase Order → Đơn mua hàng
  Campaign → Chiến dịch
  Ticket / Case → Phiếu hỗ trợ
  Dashboard → Bảng điều khiển
  Workflow → Quy trình
  Pipeline → Kênh bán hàng
  Save → Lưu, Cancel → Huỷ, Edit → Sửa, Delete → Xoá
- Keep brand/product names in English: Vtiger, CRM, SLA, API, URL, ID, OK
- Keep technical tokens unchanged: Yes/No labels in code, error codes, regex
- Keep button-style strings short. Mirror sentence case (don't add periods if source has none).
- If a string is just a placeholder/symbol/empty, return it unchanged.
"""

def translate_batch_claude(strings: list[str]) -> list[str]:
    import anthropic
    client = anthropic.Anthropic()
    payload = json.dumps(strings, ensure_ascii=False)
    msg = client.messages.create(
        model="claude-haiku-4-5-20251001",
        max_tokens=8192,
        system=SYSTEM_PROMPT,
        messages=[{
            "role": "user",
            "content": f"Translate these {len(strings)} strings. Return JSON array only.\n\n{payload}",
        }],
    )
    text = msg.content[0].text.strip()
    # Strip optional ```json fences
    if text.startswith("```"):
        text = re.sub(r"^```(?:json)?\s*", "", text)
        text = re.sub(r"\s*```$", "", text)
    arr = json.loads(text)
    if len(arr) != len(strings):
        raise RuntimeError(f"Claude returned {len(arr)} items, expected {len(strings)}")
    return arr


def translate_batch_google(strings: list[str]) -> list[str]:
    from deep_translator import GoogleTranslator
    out = []
    tr = GoogleTranslator(source="en", target="vi")
    # GoogleTranslator supports translate_batch but is unreliable; do one-by-one with placeholder protection
    for s in strings:
        # Protect placeholders & HTML by replacing with sentinels
        sentinels = []
        def stash(m):
            sentinels.append(m.group(0))
            return f"__VTPH{len(sentinels)-1}__"
        protected = re.sub(r"%(?:\d+\$)?[sdif]|<[^>]+>|\{[^}]+\}|\\[nt]", stash, s)
        try:
            vi = tr.translate(protected) or s
        except Exception as ex:
            print(f"  ! google failed: {ex}", file=sys.stderr)
            vi = s
        for i, snip in enumerate(sentinels):
            vi = vi.replace(f"__VTPH{i}__", snip)
        out.append(vi)
        time.sleep(0.05)
    return out


def pick_backend(forced: str | None) -> tuple[str, callable]:
    if forced == "claude":
        return "claude", translate_batch_claude
    if forced == "google":
        return "google", translate_batch_google
    # auto-detect
    if os.environ.get("ANTHROPIC_API_KEY"):
        try:
            import anthropic  # noqa
            return "claude", translate_batch_claude
        except ImportError:
            pass
    try:
        import deep_translator  # noqa
        return "google", translate_batch_google
    except ImportError:
        sys.exit(
            "No translation backend available. Install one:\n"
            "  pip3 install anthropic    # + export ANTHROPIC_API_KEY=...\n"
            "  pip3 install deep-translator"
        )


# ---------- Driver ---------- #

def process_file(src: Path, backend_name: str, translate_fn, force: bool, dry: bool, batch_size: int) -> tuple[int, int]:
    rel = src.relative_to(SRC_DIR)
    dst = DST_DIR / rel

    lines, entries = parse_php(src)
    if not entries:
        print(f"  {rel}: no translatable strings")
        return (0, 0)

    # Resumability: if dst already exists and not --force, skip entries already differing from en_us
    skipped = 0
    if dst.exists() and not force:
        dst_lines, dst_entries = parse_php(dst)
        dst_by_idx = {e["line_idx"]: e["val"] for e in dst_entries}
        new_entries = []
        for e in entries:
            existing = dst_by_idx.get(e["line_idx"])
            if existing is not None and existing != e["val"]:
                # Already translated (value differs from English source)
                skipped += 1
                continue
            new_entries.append(e)
        entries = new_entries
        # Start from existing dst content so prior translations are preserved
        lines = dst_lines if dst_lines else lines

    if not entries:
        print(f"  {rel}: all {skipped} strings already translated, skipping")
        return (0, skipped)

    print(f"  {rel}: translating {len(entries)} strings (skipping {skipped} already-translated)")

    translations: dict[int, str] = {}
    for batch_start in range(0, len(entries), batch_size):
        batch = entries[batch_start:batch_start + batch_size]
        if dry:
            for e in batch:
                translations[e["line_idx"]] = f"[VI] {e['val']}"
            continue
        try:
            vis = translate_fn([e["val"] for e in batch])
        except Exception as ex:
            print(f"    ! batch failed: {ex}", file=sys.stderr)
            continue
        for e, vi in zip(batch, vis):
            translations[e["line_idx"]] = vi

    new_text = reassemble(lines, entries, translations)
    if dry:
        print(f"    (dry-run) would write {dst}")
    else:
        dst.parent.mkdir(parents=True, exist_ok=True)
        dst.write_text(new_text, encoding="utf-8")
    return (len(translations), skipped)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--file", help="Single file relative to languages/en_us (e.g. Contacts.php)")
    ap.add_argument("--all", action="store_true", help="Translate every file")
    ap.add_argument("--dry-run", action="store_true", help="Don't write, prefix outputs with [VI]")
    ap.add_argument("--force", action="store_true", help="Re-translate strings even if already translated")
    ap.add_argument("--backend", choices=["claude", "google"], help="Force backend")
    ap.add_argument("--batch-size", type=int, default=40)
    args = ap.parse_args()

    if not args.file and not args.all:
        ap.error("specify --file <name> or --all")

    backend_name, translate_fn = pick_backend(args.backend)
    print(f"Backend: {backend_name}")
    print(f"Source : {SRC_DIR}")
    print(f"Dest   : {DST_DIR}")
    print()

    if args.file:
        files = [SRC_DIR / args.file]
    else:
        files = sorted(SRC_DIR.rglob("*.php"))

    total_done = total_skipped = 0
    for src in files:
        if not src.exists():
            print(f"  ! not found: {src}")
            continue
        done, skipped = process_file(src, backend_name, translate_fn, args.force, args.dry_run, args.batch_size)
        total_done += done
        total_skipped += skipped

    print()
    print(f"Translated: {total_done} strings")
    print(f"Skipped (already done): {total_skipped} strings")


if __name__ == "__main__":
    main()
