"""
Script to scrape author information from the المكتبة الشاملة (Shamela) website
and generate SQL insert statements for the `authors` table.  The script
parses each author page to extract the author's full name, splits it into
first, middle and last name fields, extracts a biographical note, attempts
to determine the author's madhhab (school of jurisprudence) by searching
for keywords in the biography, and heuristically extracts approximate
birth and death years.  Nationality is set to NULL because it is not
explicitly provided by the site.

Note: The site currently lists over 3,000 authors.  Crawling all
authors will take a significant amount of time and bandwidth.  To limit
the number of authors processed for testing, you can pass a `--limit N`
argument on the command line.  Without this option the script will
process every author link found on the index page.

Usage:
    python fill_authors.py          # process all authors (may take a long time)
    python fill_authors.py --limit 50  # process the first 50 authors only

The script prints a multi‑row INSERT statement suitable for importing
into a MySQL database using Laravel's schema for the `authors` table.  It
does not execute any SQL statements on your behalf.
"""

import argparse
import re
import sys
import os
from datetime import datetime
from typing import List, Optional, Tuple

import requests
from bs4 import BeautifulSoup

# إعداد ترميز UTF-8 لحل مشكلة النصوص العربية
os.environ['PYTHONIOENCODING'] = 'utf-8'
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')
if hasattr(sys.stderr, 'reconfigure'):
    sys.stderr.reconfigure(encoding='utf-8')


# Since the authors table now stores the full name in a single column,
# we no longer need to split the name into separate parts.  We keep
# this function as a no‑op for compatibility, but it is unused.
def split_name(full_name: str) -> Tuple[str, Optional[str], str]:
    return full_name, None, ''


def parse_biography(soup: BeautifulSoup) -> str:
    """Extract the biography text from an author page soup with better formatting."""
    header = soup.find(lambda tag: tag.name in ['h3', 'h4'] and 'تعريف بالمؤلف' in tag.get_text())
    if header:
        bio_div = header.find_next('div')
        if bio_div:
            # Preserve formatting by handling different elements
            bio_text = ''
            for element in bio_div.descendants:
                if element.name == 'br':
                    bio_text += '\n'
                elif element.name == 'p':
                    if bio_text and not bio_text.endswith('\n'):
                        bio_text += '\n'
                    bio_text += element.get_text().strip() + '\n'
                elif element.string and element.parent.name not in ['script', 'style']:
                    bio_text += element.string
            
            # Clean up extra whitespace while preserving line breaks
            lines = bio_text.split('\n')
            cleaned_lines = [line.strip() for line in lines if line.strip()]
            return '\n'.join(cleaned_lines)
    return ''


def gregorian_to_hijri(gregorian_year: int) -> int:
    """Convert Gregorian year to approximate Hijri year.
    
    Uses the formula: Hijri = Gregorian - 622 + ((Gregorian - 622) / 33)
    This gives an approximate conversion.
    """
    if gregorian_year <= 622:
        return gregorian_year  # Before Hijra, return as is
    
    hijri_approx = gregorian_year - 622 + ((gregorian_year - 622) // 33)
    return int(hijri_approx)

def extract_years(bio_text: str) -> Tuple[Optional[int], Optional[int]]:
    """Heuristically extract birth and death years from the biography.

    The function searches for the first parenthetical expression in the
    biography and extracts all digit sequences (both Arabic and Western
    numerals). All dates are converted to Hijri years.
    
    Returns: (birth_year_hijri, death_year_hijri)
    """
    match = re.search(r'\(([^)]*)\)', bio_text)
    if not match:
        return None, None
    
    inside = match.group(1)
    
    # Check for Hijri indicators
    hijri_indicators = ['هـ', 'ه', 'هجري', 'هجرية']
    gregorian_indicators = ['م', 'ميلادي', 'ميلادية']
    
    is_hijri = any(indicator in inside for indicator in hijri_indicators)
    is_gregorian = any(indicator in inside for indicator in gregorian_indicators)
    
    # Find all number sequences (Arabic digits ٠-٩ or Western digits 0-9)
    raw_numbers = re.findall(r'[0-9٠-٩]+', inside)
    
    # Convert Arabic digits to Western digits
    def ar_to_int(s: str) -> int:
        translation = str.maketrans('٠١٢٣٤٥٦٧٨٩', '0123456789')
        return int(s.translate(translation))
    
    numbers = [ar_to_int(num) for num in raw_numbers if len(ar_to_int(num).__str__()) >= 2]
    
    if len(numbers) >= 2:
        birth_year, death_year = numbers[-2], numbers[-1]
        
        # Convert to Hijri if needed
        if is_gregorian and not is_hijri:
            # Definitely Gregorian, convert to Hijri
            birth_year = gregorian_to_hijri(birth_year)
            death_year = gregorian_to_hijri(death_year)
        elif not is_hijri and not is_gregorian:
            # No clear indicators, use heuristic
            if birth_year > 1500:  # Likely Gregorian
                birth_year = gregorian_to_hijri(birth_year)
            if death_year > 1500:  # Likely Gregorian
                death_year = gregorian_to_hijri(death_year)
        # If is_hijri is True, keep as is (already Hijri)
        
        return birth_year, death_year
    
    return None, None


def detect_madhhab(bio_text: str) -> Optional[str]:
    """Detect the madhhab (Islamic school) from the biography.

    The function looks for specific keywords and returns the corresponding
    enum value.  If none are found, it returns None.
    """
    if 'الحنفي' in bio_text or 'الحنفية' in bio_text:
        return 'المذهب الحنفي'
    if 'المالكي' in bio_text or 'المالكية' in bio_text:
        return 'المذهب المالكي'
    if 'الشافعي' in bio_text or 'الشافعية' in bio_text:
        return 'المذهب الشافعي'
    if 'الحنبلي' in bio_text or 'الحنبلية' in bio_text:
        return 'المذهب الحنبلي'
    return None


def parse_author_page(author_url: str) -> Optional[Tuple[str, str, Optional[str], Optional[int], Optional[int]]]:
    """Parse an individual author page and extract relevant fields.

    Returns a tuple containing (full_name, biography, madhhab,
    birth_year_hijri, death_year_hijri).  
    If the page cannot be parsed, returns None.
    """
    try:
        # Use a browser-like User-Agent to avoid potential 403 errors from the site
        headers = {'User-Agent': 'Mozilla/5.0 (compatible; Bot/1.0; +https://example.com/bot)'}
        html = requests.get(author_url, headers=headers).text
    except Exception as exc:
        sys.stderr.write(f"Failed to fetch {author_url}: {exc}\n")
        return None
    soup = BeautifulSoup(html, 'html.parser')
    name_tag = soup.find('h1')
    if not name_tag:
        return None
    full_name = name_tag.get_text(strip=True)
    bio = parse_biography(soup)
    madhhab = detect_madhhab(bio) or None
    birth_year, death_year = extract_years(bio)
    return full_name, bio, madhhab, birth_year, death_year


def fetch_author_links(limit: Optional[int] = None) -> List[str]:
    """Fetch all author page URLs from the authors index page.

    Args:
        limit: Optional integer to limit the number of author links returned.

    Returns:
        List of author URLs (strings).
    """
    index_url = 'https://shamela.ws/authors'
    # Use a browser-like User-Agent to avoid potential 403 errors
    headers = {'User-Agent': 'Mozilla/5.0 (compatible; Bot/1.0; +https://example.com/bot)'}
    resp = requests.get(index_url, headers=headers)
    resp.raise_for_status()
    soup = BeautifulSoup(resp.text, 'html.parser')
    links = []
    for a in soup.find_all('a', href=True):
        href = a['href']
        if href.startswith('https://shamela.ws/author/'):
            links.append(href)
    # Remove duplicates while preserving order
    seen = set()
    unique_links = []
    for link in links:
        if link not in seen:
            seen.add(link)
            unique_links.append(link)
    if limit is not None:
        unique_links = unique_links[:limit]
    return unique_links


def format_date(year: Optional[int]) -> Optional[str]:
    """Convert a year integer to a 'YYYY-01-01' date string."""
    if year is None:
        return None
    try:
        return f"{int(year):04d}-01-01"
    except Exception:
        return None


def generate_sql_upsert(rows: List[Tuple[str, str, Optional[str], Optional[int], Optional[int]]]) -> str:
    """Generate INSERT ... ON DUPLICATE KEY UPDATE statements for the authors table to avoid duplicates."""
    statements = []
    
    for full_name, bio, madhhab, birth_year, death_year in rows:
        def esc(s: Optional[str]) -> str:
            if s is None:
                return 'NULL'
            return "'" + s.replace("'", "''") + "'"
        
        def esc_int(i: Optional[int]) -> str:
            if i is None:
                return 'NULL'
            return str(i)
        
        full_name_sql = esc(full_name)
        bio_sql = esc(bio) if bio else 'NULL'
        madhhab_sql = esc(madhhab)
        birth_date_sql = esc(format_date(birth_year))
        death_date_sql = esc(format_date(death_year))
        
        # Generate INSERT ... ON DUPLICATE KEY UPDATE statement
        insert_statement = f"""
INSERT INTO authors 
(full_name, author_role, biography, madhhab, birth_date, death_date, created_at, updated_at) 
VALUES ({full_name_sql}, 'مؤلف', {bio_sql}, {madhhab_sql}, {birth_date_sql}, {death_date_sql}, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    biography = COALESCE(VALUES(biography), biography),
    madhhab = COALESCE(VALUES(madhhab), madhhab),
    birth_date = COALESCE(VALUES(birth_date), birth_date),
    death_date = COALESCE(VALUES(death_date), death_date),
    updated_at = NOW();"""
        
        statements.append(insert_statement)
    
    return "\n\n".join(statements)


def main() -> int:
    parser = argparse.ArgumentParser(description="Generate SQL inserts for Shamela authors.")
    parser.add_argument('--limit', type=int, help='Limit the number of authors processed (for testing).')
    parser.add_argument('--output', type=str, help='Output file path (optional)')
    args = parser.parse_args()

    try:
        author_links = fetch_author_links(limit=args.limit)
    except Exception as exc:
        sys.stderr.write(f"Error fetching author list: {exc}\n")
        return 1
    rows: List[Tuple[str, str, Optional[str], Optional[int], Optional[int]]] = []
    for idx, url in enumerate(author_links, 1):
        info = parse_author_page(url)
        if info is None:
            sys.stderr.write(f"Warning: unable to parse author page {url}\n")
            continue
        rows.append(info)
        # Respect the limit; author_links is already limited, but break defensively
        if args.limit is not None and idx >= args.limit:
            break
    if not rows:
        sys.stderr.write("No authors parsed.\n")
        return 1
    sql = generate_sql_upsert(rows)
    
    # Write to file with UTF-8 encoding if output file specified
    if args.output:
        with open(args.output, 'w', encoding='utf-8') as f:
            f.write(sql)
    else:
        print(sql)
    return 0


if __name__ == '__main__':
    sys.exit(main())