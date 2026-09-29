#!/usr/bin/env python3
"""Verify deterministic WordPress plugin packaging."""

from __future__ import annotations

import hashlib
import pathlib
import subprocess
import tempfile
import zipfile

ROOT = pathlib.Path(__file__).resolve().parents[1]
PACKAGE = ROOT / "scripts" / "package.py"
EXPECTED = [
    "wordpress-widget-custom-css-classes/CHANGELOG.md",
    "wordpress-widget-custom-css-classes/LICENSE",
    "wordpress-widget-custom-css-classes/README.md",
    "wordpress-widget-custom-css-classes/readme.txt",
    "wordpress-widget-custom-css-classes/widget-css-classes.php",
]

with tempfile.TemporaryDirectory() as tmp:
    tmpdir = pathlib.Path(tmp)
    first = tmpdir / "first.zip"
    second = tmpdir / "second.zip"

    subprocess.run(
        ["python3", str(PACKAGE), str(first)],
        cwd=ROOT,
        check=True,
    )
    subprocess.run(
        ["python3", str(PACKAGE), str(second)],
        cwd=ROOT,
        check=True,
    )

    first_bytes = first.read_bytes()
    second_bytes = second.read_bytes()
    assert first_bytes == second_bytes, "Package output is not reproducible"

    digest = hashlib.sha256(first_bytes).hexdigest()
    assert len(digest) == 64

    with zipfile.ZipFile(first) as archive:
        assert archive.testzip() is None
        assert archive.namelist() == EXPECTED

print("OK")
