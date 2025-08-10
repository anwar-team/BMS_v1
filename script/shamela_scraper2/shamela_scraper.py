# -*- coding: utf-8 -*-
# shamela_scraper.py (FINAL: metadata + parts + index + full pages + SQL + direct insert)

from __future__ import annotations
import re, json, time
from dataclasses import dataclass, field
from typing import List, Optional, Dict, Tuple
import requests
from bs4 import BeautifulSoup

# ========= اضبط هالثوابت لتطابق جداولك =========
# books
BOOKS_PAGES_COL   = "pages_count"     # غيّر إلى "page_count" لو عندك هيك
BOOKS_VOLUMES_COL = "volumes_count"   # غيّر إلى "volume_count" لو عندك هيك
# chapters
CHAPTERS_PAGE_COL = "page_number"     # غيّر إلى "page_start" لو جدولك هيك
# pages
PAGES_TABLE       = "pages"
PAGES_PAGE_COL    = "page_number"
PAGES_CONTENT_COL = "content"

BASE_URL = "https://shamela.ws"
HEADERS  = {"User-Agent": "Mozilla/5.0 (ShamelaImporter/1.1)"}
REQ_TIMEOUT   = 30
REQUEST_DELAY = 0.35  # احترام الخادم

try:
    from slugify import slugify
except ImportError:
    import unicodedata
    def slugify(text: str) -> str:
        text = unicodedata.normalize("NFKC", text or "").strip()
        text = re.sub(r"\s+", "-", text)
        text = re.sub(r"[^\w\-]+", "", text, flags=re.U)
        return text.strip("-").lower()

# ========= نماذج البيانات =========
@dataclass
class Author:
    name: str
    slug: Optional[str] = None
    biography: Optional[str] = None
    madhhab: Optional[str] = None
    birth_date: Optional[str] = None
    death_date: Optional[str] = None
    def ensure_slug(self):
        if not self.slug and self.name:
            self.slug = slugify(self.name)

@dataclass
class Chapter:
    title: str
    page_number: Optional[int] = None
    children: List["Chapter"] = field(default_factory=list)
    volume_number: Optional[int] = None  # يُملأ لاحقًا

@dataclass
class Volume:
    number: int
    title: str
    page_start: Optional[int] = None
    page_end: Optional[int] = None

@dataclass
class Book:
    title: str
    shamela_id: str
    authors: List[Author] = field(default_factory=list)
    publisher: Optional[str] = None
    edition: Optional[str] = None
    publication_year: Optional[int] = None
    page_count: Optional[int] = None
    volume_count: Optional[int] = None
    categories: List[str] = field(default_factory=list)
    index: List[Chapter] = field(default_factory=list)
    volumes: List[Volume] = field(default_factory=list)

class ShamelaScraperError(Exception):
    pass

# ========= طلب صفحة =========
def _get_soup(url: str) -> BeautifulSoup:
    time.sleep(REQUEST_DELAY)
    r = requests.get(url, headers=HEADERS, timeout=REQ_TIMEOUT)
    r.raise_for_status()
    return BeautifulSoup(r.text, "html.parser")

# ========= بطاقة الكتاب + الفهرس =========
def _parse_info_page(book_id: str) -> Tuple[Book, BeautifulSoup]:
    soup = _get_soup(f"{BASE_URL}/book/{book_id}")

    title_tag = soup.select_one("section.page-header h1 a")
    title = (title_tag.get_text(strip=True) if title_tag else "").strip()
    if not title:
        raise ShamelaScraperError("تعذّر استخراج عنوان الكتاب")

    authors: List[Author] = []
    for a in soup.select("section.page-header .container a[href*='/author/']"):
        name = a.get_text(strip=True)
        if name:
            au = Author(name=name); au.ensure_slug(); authors.append(au)

    cats: List[str] = []
    for a in soup.select("ol.breadcrumb a"):
        txt = a.get_text(strip=True)
        if txt and txt not in ("الرئيسية", "أقسام الكتب"):
            cats.append(txt)

    publisher = edition = None
    publication_year = None
    pages_or_parts = None  # ("pages"| "parts", value)

    nass = soup.select_one("div.nass") or soup
    lines = []
    for el in nass.find_all(["p", "li", "div", "span"], recursive=True):
        t = el.get_text(" ", strip=True)
        if ":" in t and len(t) < 200:
            lines.append(t)

    for line in lines:
        key, val = [s.strip() for s in line.split(":", 1)]
        if not val:
            continue
        if key in ("الناشر", "دار النشر"): publisher = val
        elif key in ("الطبعة", "الطبعةُ"):  edition = val
        elif key in ("سنة النشر", "عام النشر"):
            m = re.search(r"(\d{3,4})", val)
            if m:
                try: publication_year = int(m.group(1))
                except: pass
        elif key in ("عدد الصفحات",):
            m = re.search(r"(\d+)", val); 
            if m: pages_or_parts = ("pages", int(m.group(1)))
        elif key in ("عدد الأجزاء","الأجزاء"):
            m = re.search(r"(\d+)", val); 
            if m: pages_or_parts = ("parts", int(m.group(1)))

    book = Book(
        title=title,
        shamela_id=str(book_id),
        authors=authors,
        publisher=publisher,
        edition=edition,
        publication_year=publication_year,
        page_count=pages_or_parts[1] if (pages_or_parts and pages_or_parts[0]=="pages") else None,
        volume_count=pages_or_parts[1] if (pages_or_parts and pages_or_parts[0]=="parts") else None,
        categories=cats
    )
    return book, soup

def _pick_title_and_page(anchor, book_id: str) -> Tuple[str, Optional[int]]:
    if not anchor:
        return "", None
    title = anchor.get_text(strip=True)
    href  = anchor.get("href") or ""
    if f"/book/{book_id}/" in href:
        m = re.search(rf"/book/{book_id}/(\d+)", href)
        page = int(m.group(1)) if m else None
    else:
        page = None
    return title, page

def _parse_chapter_list(ul_tag, book_id: str) -> List[Chapter]:
    chapters: List[Chapter] = []
    for li in ul_tag.find_all("li", recursive=False):
        anchor = None
        for a in li.find_all("a", href=True):
            if f"/book/{book_id}/" in (a.get("href") or ""):
                anchor = a; break
        if anchor is None:
            anchor = li.find("a")

        title, page = _pick_title_and_page(anchor, book_id)
        if title in ("نسخ الرابط", "نشر لفيسيوك", "نشر لتويتر"):
            continue
        title = title.lstrip("-").strip()

        child_ul = li.find("ul")
        children = _parse_chapter_list(child_ul, book_id) if child_ul else []
        if title:
            chapters.append(Chapter(title=title, page_number=page, children=children))
    return chapters

def _parse_index_from_info_page(book_id: str, soup: BeautifulSoup) -> List[Chapter]:
    c = soup.select_one("div.betaka-index")
    if not c: return []
    ul = c.find("ul")
    if not ul: return []
    return _parse_chapter_list(ul, book_id)

# ========= آخر صفحة =========
def _detect_last_page(book_id: str) -> Optional[int]:
    soup = _get_soup(f"{BASE_URL}/book/{book_id}/1")
    max_page = 1
    for a in soup.select("a[href*='/book/']"):
        href = a.get("href") or ""
        m = re.search(rf"/book/{book_id}/(\d+)", href)
        if m:
            pg = int(m.group(1))
            if pg > max_page: max_page = pg
    for a in soup.select("ul.pagination a, .pagination a"):
        txt = (a.get_text() or "").strip()
        if txt in ("الأخير", ">>", "آخر"):
            m = re.search(rf"/book/{book_id}/(\d+)", a.get("href") or "")
            if m:
                pg = int(m.group(1))
                if pg > max_page: max_page = pg
    return max_page if max_page >= 1 else None

# ========= كشف الأجزاء ونطاقاتها من صفحة 1 =========
def _detect_parts_ranges(book_id: str) -> Tuple[List[Volume], Optional[int]]:
    s = _get_soup(f"{BASE_URL}/book/{book_id}/1")

    last_page = 1
    for a in s.select("a[href*='/book/']"):
        m = re.search(rf"/book/{book_id}/(\d+)", a.get("href") or "")
        if m:
            pg = int(m.group(1))
            if pg > last_page: last_page = pg
    if last_page < 1:
        last_page = None

    parts_links = []
    parts_links += s.select("#fld_part_top ~ div ul[role='menu'] li a[href]")
    if not parts_links:
        parts_links += s.select("div.dropdown-menu a[href]")
    if not parts_links:
        for a in s.select("a[href*='/book/']"):
            if "الجزء" in (a.get_text(strip=True) or ""):
                parts_links.append(a)

    parts: List[Tuple[str, Optional[int]]] = []
    for a in parts_links:
        label = a.get_text(strip=True)
        href  = a.get("href") or ""
        m = re.search(rf"/book/{book_id}/(\d+)", href)
        pstart = int(m.group(1)) if m else None
        if label and "الجزء" in label:
            parts.append((label, pstart))

    parts = [(t, p) for (t, p) in parts if t]
    parts.sort(key=lambda x: (x[1] if x[1] is not None else 10**9))

    volumes: List[Volume] = []
    if parts:
        for i, (label, pstart) in enumerate(parts):
            if pstart is None: continue
            if i+1 < len(parts):
                nstart = parts[i+1][1] or pstart
                pend   = (nstart - 1) if (nstart and nstart > pstart) else None
            else:
                pend   = last_page
            volumes.append(Volume(number=i+1, title=label, page_start=pstart, page_end=pend))
    else:
        volumes.append(Volume(number=1, title="المجلد", page_start=1, page_end=(last_page if last_page and last_page>1 else None)))

    return volumes, (last_page if last_page and last_page > 1 else None)

def _assign_chapters_to_volumes(chapters: List[Chapter], volumes: List[Volume]) -> None:
    def vol_for_page(pg: Optional[int]) -> Optional[int]:
        if pg is None: return None
        for v in volumes:
            s = v.page_start or 1
            e = v.page_end or 10**9
            if s <= pg <= e:
                return v.number
        return None
    def walk(nodes: List[Chapter]):
        for ch in nodes:
            ch.volume_number = vol_for_page(ch.page_number)
            if ch.children:
                walk(ch.children)
    walk(chapters)

# ========= API رئيسي =========
def parse_book_page(book_id: str) -> Book:
    book, soup = _parse_info_page(book_id)
    book.index = _parse_index_from_info_page(book_id, soup)

    vols, last_page = _detect_parts_ranges(book_id)
    # حدّث إجمالي الصفحات إن وجدنا أكبر صفحة
    last_page2 = _detect_last_page(book_id)
    if last_page2 and (not last_page or last_page2 > last_page):
        last_page = last_page2

    if last_page and (not book.page_count or book.page_count < last_page):
        book.page_count = last_page

    # لا نُخفّض العدد إن البطاقة قالت >1
    if vols and len(vols) > 1:
        book.volumes = vols
        book.volume_count = len(vols)
    else:
        if (book.volume_count or 0) > 1:
            book.volumes = [Volume(number=i+1, title=f"الجزء {i+1}") for i in range(book.volume_count)]
        else:
            book.volume_count = 1
            book.volumes = [Volume(number=1, title="المجلد", page_start=1, page_end=last_page)]

    _assign_chapters_to_volumes(book.index, book.volumes)
    return book

# ========= SQL: books/authors/volumes/chapters =========
def flatten_chapters(chs: List[Chapter], level: int = 0) -> List[Dict]:
    rows = []
    for ch in chs:
        rows.append({
            "title": ch.title.strip(),
            "page_number": ch.page_number,
            "level": level,
            "volume_number": ch.volume_number
        })
        if ch.children:
            rows.extend(flatten_chapters(ch.children, level+1))
    return rows

def mysql_escape(val: Optional[str]) -> str:
    if val is None: return "NULL"
    return "'" + str(val).replace("\\", "\\\\").replace("'", "\\'") + "'"

def generate_insert_sql(book: Book) -> str:
    bslug = slugify(book.title or f"book-{book.shamela_id}")
    cats  = json.dumps(book.categories or [], ensure_ascii=False)

    lines = []
    lines.append("START TRANSACTION;")
    lines.append("SET NAMES utf8mb4;")
    lines.append("SET @book_id := 0;")

    # books
    lines.append("-- books")
    lines.append(f"""
INSERT INTO books (title, slug, publisher, edition, publication_year, {BOOKS_PAGES_COL}, {BOOKS_VOLUMES_COL}, shamela_id, categories, source_url)
VALUES ({mysql_escape(book.title)}, {mysql_escape(bslug)}, {mysql_escape(book.publisher)}, {mysql_escape(book.edition)},
        {book.publication_year if book.publication_year else "NULL"},
        {book.page_count if book.page_count else "NULL"},
        {book.volume_count if book.volume_count else "NULL"},
        {mysql_escape(book.shamela_id)},
        {mysql_escape(cats)},
        {mysql_escape(f"{BASE_URL}/book/{book.shamela_id}")})
ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id),
    title=VALUES(title), publisher=VALUES(publisher), edition=VALUES(edition),
    publication_year=VALUES(publication_year), {BOOKS_PAGES_COL}=VALUES({BOOKS_PAGES_COL}),
    {BOOKS_VOLUMES_COL}=VALUES({BOOKS_VOLUMES_COL}), categories=VALUES(categories), source_url=VALUES(source_url);
SET @book_id := LAST_INSERT_ID();
""".strip())

    # authors + author_book
    lines.append("-- authors + author_book")
    for a in (book.authors or []):
        a.ensure_slug()
        lines.append("SET @a_id := 0;")
        lines.append(f"""
INSERT INTO authors (name, slug, biography, madhhab, birth_date, death_date)
VALUES ({mysql_escape(a.name)}, {mysql_escape(a.slug)}, {mysql_escape(a.biography)}, {mysql_escape(a.madhhab)}, {mysql_escape(a.birth_date)}, {mysql_escape(a.death_date)})
ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), name=VALUES(name);
SET @a_id := LAST_INSERT_ID();
INSERT IGNORE INTO author_book (book_id, author_id, role, is_main) VALUES (@book_id, @a_id, 'author', 1);
""".strip())

    # volumes
    lines.append("-- volumes")
    for v in (book.volumes or [Volume(1, "المجلد")]):
        lines.append(f"""
INSERT INTO volumes (book_id, number, title, page_start, page_end)
VALUES (@book_id, {v.number}, {mysql_escape(v.title)}, {v.page_start if v.page_start else "NULL"}, {v.page_end if v.page_end else "NULL"})
ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), title=VALUES(title), page_start=VALUES(page_start), page_end=VALUES(page_end);
""".strip())

    # chapters
    lines.append("-- chapters")
    flat = flatten_chapters(book.index or [])
    for ch in flat:
        vol_no = ch["volume_number"]
        vol_id_expr = "NULL"
        if vol_no:
            vol_id_expr = f"(SELECT id FROM volumes WHERE book_id=@book_id AND number={vol_no} LIMIT 1)"
        page_expr = ch["page_number"] if ch["page_number"] else "NULL"
        lines.append(f"""
INSERT INTO chapters (book_id, volume_id, title, {CHAPTERS_PAGE_COL}, page_end, parent_id, level)
VALUES (@book_id, {vol_id_expr}, {mysql_escape(ch['title'])}, {page_expr}, NULL, NULL, {ch['level']});
""".strip())

    lines.append("COMMIT;")
    return "\n".join(lines)

# ========= الصفحات: جلب وتوليد SQL =========
def fetch_page_content(book_id: str, page_no: int, as_html: bool = False) -> str:
    url  = f"{BASE_URL}/book/{book_id}/{page_no}"
    soup = _get_soup(url)

    candidates = [
        soup.find(id="book"),
        soup.select_one("div#text"),
        soup.select_one("article"),
        soup.select_one("div.reader-text"),
        soup.select_one("div.col-md-9")
    ]
    main = next((c for c in candidates if c), None) or soup.find("body") or soup

    # تنظيف عناصر مزعجة
    for bad in main.select("script, style, nav, .share, .social, .ad, .navbar, .pagination"):
        bad.decompose()

    if as_html:
        return main.decode()

    text = main.get_text("\n", strip=True)
    text = re.sub(r"\n{3,}", "\n\n", text)
    return text

def fetch_pages_for_book(book: Book, as_html: bool = False) -> List[Dict]:
    last_page = book.page_count or _detect_last_page(book.shamela_id) or 1
    vols = book.volumes or [Volume(1, "المجلد", 1, last_page)]

    def vol_for_page(pg):
        for v in vols:
            s, e = v.page_start or 1, v.page_end or 10**9
            if s <= pg <= e:
                return v.number
        return None

    pages = []
    for p in range(1, last_page + 1):
        txt = fetch_page_content(book.shamela_id, p, as_html=as_html)
        pages.append({"page_number": p, "volume_number": vol_for_page(p), "content": txt})
    return pages

def generate_pages_insert_sql(book: Book, pages: List[Dict]) -> str:
    """
    INSERT لجدول الصفحات:
    - book_id من books
    - volume_id عبر (book_id, number)
    - chapter_id: أقرب فصل يبدأ قبل/عند الصفحة (subquery)
    """
    lines = []
    lines.append("START TRANSACTION;")
    lines.append("SET NAMES utf8mb4;")
    lines.append(f"SET @book_id := (SELECT id FROM books WHERE shamela_id='{book.shamela_id}' LIMIT 1);")
    lines.append("SET @vol_id := NULL;")
    lines.append("SET @chap_id := NULL;")

    for row in pages:
        vol_no = row.get("volume_number")
        if vol_no:
            lines.append(f"SET @vol_id := (SELECT id FROM volumes WHERE book_id=@book_id AND number={vol_no} LIMIT 1);")
        else:
            lines.append("SET @vol_id := NULL;")

        lines.append(f"""
SET @chap_id := (
  SELECT id FROM chapters
  WHERE book_id=@book_id AND {CHAPTERS_PAGE_COL} IS NOT NULL AND {CHAPTERS_PAGE_COL} <= {row['page_number']}
  ORDER BY {CHAPTERS_PAGE_COL} DESC
  LIMIT 1
);""".strip())

        content = row["content"].replace("\\","\\\\").replace("'","\\'")
        lines.append(
            f"INSERT INTO {PAGES_TABLE} (book_id, volume_id, chapter_id, {PAGES_PAGE_COL}, {PAGES_CONTENT_COL}) "
            f"VALUES (@book_id, @vol_id, @chap_id, {row['page_number']}, '{content}') "
            f"ON DUPLICATE KEY UPDATE {PAGES_CONTENT_COL}=VALUES({PAGES_CONTENT_COL});"
        )

    lines.append("COMMIT;")
    return "\n".join(lines)

# ========= إدخال مباشر (اختياري) =========
def insert_into_mysql(book: Book, mysql_cfg: Dict) -> str:
    import mysql.connector
    sql_script = generate_insert_sql(book)
    conn = mysql.connector.connect(
        host=mysql_cfg["host"], port=mysql_cfg["port"],
        user=mysql_cfg["user"], password=mysql_cfg["password"],
        database=mysql_cfg["database"], charset="utf8mb4", use_unicode=True
    )
    try:
        cur = conn.cursor()
        for stmt in [s for s in sql_script.split(";\n") if s.strip()]:
            cur.execute(stmt)
        conn.commit()
    finally:
        cur.close(); conn.close()
    return sql_script

def insert_pages_into_mysql(book: Book, pages: List[Dict], mysql_cfg: Dict) -> str:
    import mysql.connector
    sql_script = generate_pages_insert_sql(book, pages)
    conn = mysql.connector.connect(
        host=mysql_cfg["host"], port=mysql_cfg["port"],
        user=mysql_cfg["user"], password=mysql_cfg["password"],
        database=mysql_cfg["database"], charset="utf8mb4", use_unicode=True
    )
    try:
        cur = conn.cursor()
        for stmt in [s for s in sql_script.split(";\n") if s.strip()]:
            cur.execute(stmt)
        conn.commit()
    finally:
        cur.close(); conn.close()
    return sql_script

# ========= تشغيل من الطرفية (اختياري) =========
if __name__ == "__main__":
    import argparse
    p = argparse.ArgumentParser()
    p.add_argument("book_id")
    p.add_argument("--out", choices=["json","sql","pages"], default="json",
                   help="json: كتاب، sql: INSERT (كتاب/مؤلف/أجزاء/فهرس)، pages: INSERT للصفحات")
    p.add_argument("--html", action="store_true", help="حفظ الصفحات كـ HTML بدل النص")
    args = p.parse_args()

    bk = parse_book_page(args.book_id)
    if args.out == "json":
        print(json.dumps({
            "title": bk.title,
            "shamela_id": bk.shamela_id,
            "authors": [a.__dict__ for a in bk.authors],
            "publisher": bk.publisher,
            "edition": bk.edition,
            "publication_year": bk.publication_year,
            BOOKS_PAGES_COL: bk.page_count,
            BOOKS_VOLUMES_COL: bk.volume_count,
            "categories": bk.categories,
            "volumes": [v.__dict__ for v in bk.volumes],
            "index": [{"title": c["title"], "page": c["page_number"], "vol": c["volume_number"]} for c in flatten_chapters(bk.index)]
        }, ensure_ascii=False, indent=2))
    elif args.out == "sql":
        print(generate_insert_sql(bk))
    else:
        pages = fetch_pages_for_book(bk, as_html=args.html)
        print(generate_pages_insert_sql(bk, pages))
