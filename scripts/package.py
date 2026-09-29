#!/usr/bin/env python3
"""Build a reproducible WordPress installation ZIP from runtime files only."""

from __future__ import annotations

import pathlib
import re
import sys
import zipfile

ROOT = pathlib.Path(__file__).resolve().parents[1]
PLUGIN_DIR = "wordpress-widget-custom-css-classes"
MAIN_FILE = "widget-css-classes.php"
FILES = [
    MAIN_FILE,
    "includes/class-widget-css-classes-updater.php",
    "readme.txt",
    "README.md",
    "CHANGELOG.md",
    "LICENSE",
]

header = (ROOT / MAIN_FILE).read_text(encoding="utf-8")
version_match = re.search(r"^ \* Version: (\d+\.\d+\.\d+)$", header, re.M)
assert version_match, "Plugin Version header is missing or invalid"
version = version_match.group(1)

readme = (ROOT / "readme.txt").read_text(encoding="utf-8")
assert f"Stable tag: {version}\n" in readme, "readme.txt Stable tag does not match plugin version"

license_text = (ROOT / "LICENSE").read_text(encoding="utf-8")
assert "GNU GENERAL PUBLIC LICENSE" in license_text
assert "Version 2, June 1991" in license_text
assert len(license_text) > 10000

for name in FILES:
    path = ROOT / name
    assert path.is_file(), f"Required package file is missing: {name}"

archive = pathlib.Path(
    sys.argv[1]
    if len(sys.argv) > 1
    else ROOT / "dist" / f"{PLUGIN_DIR}.zip"
)
archive.parent.mkdir(parents=True, exist_ok=True)

with zipfile.ZipFile(
    archive,
    "w",
    compression=zipfile.ZIP_DEFLATED,
    compresslevel=9,
) as out:
    for name in sorted(FILES):
        info = zipfile.ZipInfo(
            f"{PLUGIN_DIR}/{name}",
            (2026, 1, 1, 0, 0, 0),
        )
        info.compress_type = zipfile.ZIP_DEFLATED
        info.external_attr = 0o100644 << 16
        out.writestr(info, (ROOT / name).read_bytes())

with zipfile.ZipFile(archive) as built:
    assert built.testzip() is None
    names = built.namelist()
    expected = [f"{PLUGIN_DIR}/{name}" for name in sorted(FILES)]
    assert names == expected, f"Unexpected package contents: {names!r}"

print(f"Built WordPress Widget Custom CSS Classes {version}: {archive}")
