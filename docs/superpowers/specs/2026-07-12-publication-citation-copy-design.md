# Publication Citation Copy (Reviewer Helper)

**Date:** 2026-07-12  
**Status:** Approved for implementation (pending user review of this spec)  
**Surface:** Existing `/publications` page (Approach A)

## Problem

When peer-reviewing papers, the portfolio owner needs to quickly find relevant own works (journal, conference, book/chapter) by keyword and paste short citation lines into review comments recommending those works to authors.

Today `/publications` has search and type tabs, but search is tab-scoped in the UI, and there is no copy-citation or multi-select clipboard workflow.

## Goals

1. Search **across all recommendable types** in one pass (not limited to the active tab).
2. Copy **short recommend lines** (title + year + DOI/link).
3. Support **both**:
   - Copy all matches from the current search
   - Checkbox select a subset, then Copy selected
4. Keep the change small: reuse existing publication data and page; no new DB columns.

## Non-goals

- Full APA/MLA/IEEE bibliography export
- BibTeX/RIS download (can be added later)
- Including `in_progress` / unpublished drafts in recommend mode
- A separate `/recommend` URL (deferred; can split later if needed)
- Admin-only Filament tool

## User flow

1. Open `/publications`.
2. Switch to a **Recommend** tab (or equivalent mode alongside existing type tabs).
3. Type a keyword (title / authors / venue). Results show matching journal + conference + book + book_chapter items in one flat list.
4. Optionally tick individual papers.
5. Click **Copy all matches** or **Copy selected**.
6. Paste into the review comment. Toast confirms “Copied N citations”.

Helpers: **Select all** / **Clear** selection.

## Citation format (short line)

One line per publication:

```text
{Title} ({Year}). {Link}
```

Link resolution order:

1. DOI URL (`https://doi.org/{doi}` if DOI present)
2. Else `url`
3. Else `pdf_url`
4. Else omit the link (still copy title + year)

Example:

```text
Deep learning for medical imaging (2024). https://doi.org/10.1234/example
Survey of transformer models (2023). https://example.org/paper
```

Multiple citations: one line per item, separated by newlines (easy paste into email/review forms).

## UI design

### Mode entry

Add a tab (or mode control) labeled **Recommend** next to existing tabs:

- Journals | Conferences | Books & Chapters | In Progress | Summary | **Recommend**

Entering Recommend:

- Shows flat cross-type results (or empty state prompting search).
- Existing type tabs remain unchanged for normal browsing.

### Recommend panel

- Reuse (or mirror) the page search box; when in Recommend mode, filter the combined set client-side and/or via the same `?q=` query.
- Result rows:
  - Checkbox
  - Type badge (Journal / Conference / Book / Book Chapter)
  - Year
  - Title
  - Short preview of the citation line (muted)
- Toolbar above results:
  - Select all / Clear
  - Copy selected (disabled when none checked)
  - Copy all matches (disabled when zero matches)
- Feedback: brief “Copied N” state (Alpine clipboard pattern already used on bio extras).

### Search scope

Recommend mode includes types: `journal`, `conference`, `book`, `book_chapter`.

Excludes: `in_progress`, and optionally `preprint` / `other` unless they appear in visible published lists today—**default exclude** preprint/other for reviewer recommendations unless product later expands.

Only `is_visible` publications (same as public page).

## Backend / model

Add on `App\Models\Publication`:

```php
public function toShortCitation(): string
```

Builds the short line from `title`, `year`, and `primaryUrl()` / DOI helpers. No schema migration.

Controller: no new endpoint required if filtering stays on the publications page with existing data passed to the view. Optionally pass a pre-merged `$recommendable` collection (visible + allowed types, sorted by year desc) for the Recommend tab.

## Clipboard behavior

- Use `navigator.clipboard.writeText` with fallback if needed (same pattern as bio “Copy all”).
- **Copy all matches:** join `toShortCitation()` for every current match.
- **Copy selected:** join only checked IDs / rows.
- Preserve order: year descending, then title (match list order).

## Edge cases

| Case | Behavior |
|------|----------|
| Empty search in Recommend | Show all recommendable pubs, or prompt “Type keywords to filter” — **prefer show all** so Select all + Copy works without a query |
| Zero matches | Disable copy buttons; show “No matching publications” |
| Missing DOI and URL | Copy `Title (Year).` without link |
| Duplicate titles | Still list separately (different years/DOIs) |

## Implementation sketch (files)

| File | Change |
|------|--------|
| `app/Models/Publication.php` | `toShortCitation()` |
| `app/Http/Controllers/PortfolioController.php` | Pass recommendable collection if useful |
| `resources/views/publications/index.blade.php` | Recommend tab + toolbar + Alpine selection/copy |
| `resources/views/partials/publication-card.blade.php` | Optional: leave alone; Recommend can use a lighter row partial |

No Filament changes required.

## Success criteria

- From `/publications` → Recommend, keyword search finds hits across journal/conference/book/chapter in one list.
- Copy all and Copy selected both produce newline-separated short citations on the clipboard.
- Existing type tabs and Summary behavior unchanged.
- No new database migration.

## Out of scope follow-ups

- APA / IEEE style toggle
- Dedicated `/recommend` bookmark URL
- Email “suggested reading” template wrapper
- Persist last search in session
