# BookResource Structure (BMS)

- **Book**: Main entity. Fields: title, description, slug, isbn, published_year, published_year_type, publisher_id, source_url, cover_image, visibility, status, book_section_id. Relations: authors (pivot with role, is_main, display_order), volumes, pages.

- **Tabs**:
  1. Basic Info: title, description, slug, isbn
  2. Categories & Authors: book_section_id, authors (repeater, each with author_id, role, is_main, display_order)
  3. Volumes & Chapters: volumes (repeater, each with number, title, description, chapters)
      - Chapters: repeater inside volume, each with chapter_number, title, summary
  4. Pages: Only visible if book is saved. pages (repeater, each with page_number, volume_id, chapter_id, title, content, image, notes, is_published)
  5. If book not saved: Placeholder tab with message to save first

- **Counts**: pages_count, volumes_count auto-calculated (not user-editable)

- **Validation**: required, unique, maxLength, numeric, relationship integrity

- **UI**: Filament Tabs, Grid, Section, Repeater, Select, FileUpload, RichEditor, Toggle, Placeholder

- **Logic**: Cannot add pages before saving book. All nested data (volumes, chapters, pages) managed via repeaters. Authors managed with roles and order. All relationships are Eloquent-based.

- **Other Entities**: Publisher (name, country, email, phone, website, description, is_active), BookSection (name, description, parent_id, sort_order, is_active, slug)

- **All labels, options, and messages are in Arabic.**

---

This structure enables a multi-level book system: Book → Volumes → Chapters → Pages, with full author, publisher, and section management, and strict data integrity via UI and backend.
