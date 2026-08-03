#!/usr/bin/env python3
"""Scan myclient for mojibake (UTF-8 misread as CP1251) vs valid Ukrainian UTF-8."""
from __future__ import annotations

import os
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SKIP_DIRS = {"vendor", "node_modules", ".git", "screenshot", "cache"}
EXTS = {".php", ".txt", ".md", ".json", ".html", ".htm", ".css", ".js", ".xml", ".htaccess", ".csv"}

# "РњРѕ" — classic mojibake of "Мо" when UTF-8 is decoded as CP1251 then re-saved as UTF-8
BAD = bytes([0xD0, 0xA0, 0xD0, 0x9C, 0xD0, 0xA0, 0xD0, 0xBE])
# Valid "Мої"
GOOD = bytes([0xD0, 0x9C, 0xD0, 0xBE, 0xD1, 0x97])
# Other mojibake markers
BAD2 = "ÐœÐ".encode("utf-8")  # Latin-1 style mojibake of М
BAD3 = "Â·".encode("utf-8")  # middle-dot mojibake of ·


def main() -> int:
    bad_files: list[str] = []
    good_files: list[str] = []
    bom_files: list[str] = []
    scanned = 0

    for dp, dns, fns in os.walk(ROOT):
        dns[:] = [d for d in dns if d not in SKIP_DIRS]
        for fn in fns:
            ext = os.path.splitext(fn)[1].lower()
            if ext not in EXTS and fn != ".htaccess":
                continue
            path = os.path.join(dp, fn)
            try:
                size = os.path.getsize(path)
                if size > 900_000:
                    continue
                data = open(path, "rb").read()
            except OSError:
                continue
            scanned += 1
            rel = os.path.relpath(path, ROOT)
            if data[:3] == b"\xef\xbb\xbf":
                bom_files.append(rel)
            if BAD in data or BAD2 in data:
                bad_files.append(rel)
            if GOOD in data:
                good_files.append(rel)

    print(f"scanned={scanned}")
    print(f"valid_uk_Moi_files={len(good_files)}")
    print(f"mojibake_files={len(bad_files)}")
    print(f"bom_files={len(bom_files)}")
    for x in bad_files:
        print(f"BAD {x}")
    for x in bom_files[:30]:
        print(f"BOM {x}")
    for x in good_files[:20]:
        print(f"OK  {x}")

    llms = os.path.join(ROOT, "llms.txt")
    if os.path.isfile(llms):
        b = open(llms, "rb").read()
        print(
            "llms.txt valid=",
            GOOD in b,
            " mojibake=",
            BAD in b,
            " size=",
            len(b),
        )
    return 1 if bad_files else 0


if __name__ == "__main__":
    sys.exit(main())
