# Changelog

All notable changes to this project will be documented in this file.

## [v3.0.0] - 2026-03-09

### Added
- **Locale / i18n** — `->locale('ar')` for date picker display language
- **Dependent fields** — `->minDateFrom('field')` / `->maxDateFrom('field')` for dynamic date constraints
- **Export format** — `->exportFormat('comma|pipe|semicolon|newline')` for CSV exports
- **Range mode** — `->rangeMode()` to pick start and end date, auto-fill all dates between
- **Recurrence patterns** — `->allowRecurrence()` with daily, weekly, biweekly, monthly, weekdays, weekends
- **Calendar heatmap view** — `->calendarView()` for mini calendar on detail page
- **Date presets** — `->presets([...])` for predefined quick selections with arrays or callables
- **Bulk actions** — `BulkAddDates` and `BulkRemoveDates` Nova Actions
- **Callback hooks** — `->beforeSave()`, `->afterResolve()`, `->onDateSelected()`
- **Max/min selections** — `->maxSelections()` / `->minSelections()` to limit date count
- **Index truncation** — `->maxDisplayOnIndex()` with "+N more" badge
- **Readonly/disabled** support in form field
- **Full dark mode** CSS for all components
- **LICENSE.md** file
- **PHPUnit tests** — 70 tests with 172 assertions
- **GitHub Actions CI** workflow

### Changed
- **Replaced view-ui-plus with v-calendar** — bundle reduced from 1.6MB to 185KB (88% smaller)
- **PHP 8.1+ required** — added type hints and return types throughout
- **Expanded date format converter** — supports D, l, M, F, H, h, G, g, i, s, A, a tokens
- **Improved fill() serialization** — dates sent as clean ISO strings instead of raw JS Date objects
- **Removed lodash** from webpack ProvidePlugin
- **Updated composer.json** — better description, keywords, dev dependencies, autoload-dev, test script
- **Updated README** — comprehensive documentation with examples for every feature

### Fixed
- Removed debug `dd()` comment from resolveAttribute
- Fixed `isoFormat()` → `format()` for proper Carbon usage
- Added try/catch for graceful invalid date handling
- Added `unique()` to prevent duplicate dates
- Proper null/empty attribute handling

## [v2.0] - Previous release

- Initial Nova 4/5 support with view-ui-plus

## [v1.0.2] - Initial releases

- Basic multiple date picker functionality
