# Nova Multiple DatePicker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ghanem/multiple-date-picker.svg?style=flat-square)](https://packagist.org/packages/ghanem/multiple-date-picker)
[![Total Downloads](https://img.shields.io/packagist/dt/ghanem/multiple-date-picker.svg?style=flat-square)](https://packagist.org/packages/ghanem/multiple-date-picker)

A [Laravel Nova](https://nova.laravel.com/) field for selecting multiple dates with a beautiful date picker interface.

## Requirements

- `php: ^8.1`
- `laravel/nova: ^4.0|^5.0`

## Features

- Select multiple dates in a single field
- Lightweight (~185KB) using [v-calendar](https://vcalendar.io/)
- Configurable display and storage date formats
- Min/max date constraints
- Disable specific dates
- Min/max selection limits
- Automatic date sorting
- Custom placeholder text
- Index view truncation with "+N more" badge
- Full dark mode support
- Readonly/disabled state support
- Duplicate date prevention
- Graceful handling of invalid dates
- Locale / i18n support
- Dependent fields (dynamic min/max from other fields)
- Export-friendly format (comma, pipe, semicolon, newline)
- Date range mode (pick start & end, auto-fill between)
- Recurrence patterns (daily, weekly, biweekly, monthly, weekdays, weekends)
- Calendar heatmap view on detail page
- Date presets for quick selection
- Bulk actions (add/remove dates across resources)
- Before-save and after-resolve callback hooks
- JavaScript date-selected callback

## Installation

```bash
composer require ghanem/multiple-date-picker
```

## Usage

### Basic Usage

```php
use Ghanem\MultipleDatePicker\MultipleDatePicker;

MultipleDatePicker::make('Event Dates', 'event_dates'),
```

### Configuration Options

```php
// Custom display date format (default: d/m/Y)
MultipleDatePicker::make('Dates', 'dates')
    ->dateFormat('Y-m-d'),

// Supported format tokens: d, j, D, l, m, n, M, F, Y, y

// Custom storage format (default: Y-m-d)
MultipleDatePicker::make('Dates', 'dates')
    ->storageFormat('d/m/Y'),

// Min and max date constraints
MultipleDatePicker::make('Dates', 'dates')
    ->minDate('2026-01-01')
    ->maxDate('2026-12-31'),

// Disable specific dates
MultipleDatePicker::make('Dates', 'dates')
    ->disabledDates(['2026-03-05', '2026-03-10']),

// Limit number of selectable dates
MultipleDatePicker::make('Dates', 'dates')
    ->minSelections(1)
    ->maxSelections(10),

// Sort dates automatically
MultipleDatePicker::make('Dates', 'dates')
    ->sorted(),

// Custom placeholder (Nova built-in)
MultipleDatePicker::make('Dates', 'dates')
    ->placeholder('Select your dates...'),

// Truncate dates on index view (show "+N more" badge)
MultipleDatePicker::make('Dates', 'dates')
    ->maxDisplayOnIndex(3),
```

### Locale / i18n

```php
// Set locale for date picker display
MultipleDatePicker::make('Dates', 'dates')
    ->locale('ar'),
```

### Dependent Fields

Dynamically set min/max dates based on other fields on the same form:

```php
Date::make('Start Date', 'start_date'),

MultipleDatePicker::make('Event Dates', 'event_dates')
    ->minDateFrom('start_date'),

Date::make('End Date', 'end_date'),

MultipleDatePicker::make('Available Dates', 'available_dates')
    ->minDateFrom('start_date')
    ->maxDateFrom('end_date'),
```

### Export Format

Control how dates appear in Nova CSV exports:

```php
// Comma-separated: "01/03/2026, 15/03/2026"
MultipleDatePicker::make('Dates', 'dates')
    ->exportFormat('comma'),

// Pipe-separated: "01/03/2026 | 15/03/2026"
MultipleDatePicker::make('Dates', 'dates')
    ->exportFormat('pipe'),

// Semicolon: "01/03/2026; 15/03/2026"
MultipleDatePicker::make('Dates', 'dates')
    ->exportFormat('semicolon'),

// Newline-separated
MultipleDatePicker::make('Dates', 'dates')
    ->exportFormat('newline'),
```

### Date Range Mode

Pick a start and end date, automatically store all dates in between:

```php
MultipleDatePicker::make('Booking Dates', 'booking_dates')
    ->rangeMode(),
```

### Recurrence Patterns

Generate dates based on recurring patterns:

```php
// Allow all patterns
MultipleDatePicker::make('Schedule', 'schedule_dates')
    ->allowRecurrence(),

// Allow only specific patterns
MultipleDatePicker::make('Schedule', 'schedule_dates')
    ->allowRecurrence(['weekly', 'monthly']),

// Available patterns: daily, weekly, biweekly, monthly, weekdays, weekends
```

### Calendar Heatmap View

Display a mini calendar on the detail page instead of badges:

```php
MultipleDatePicker::make('Dates', 'dates')
    ->calendarView(),
```

### Date Presets

Predefined date selections for quick selection:

```php
MultipleDatePicker::make('Dates', 'dates')
    ->presets([
        ['label' => 'Next 7 Days', 'dates' => fn () => collect(range(0, 6))
            ->map(fn ($i) => now()->addDays($i)->format('Y-m-d'))
            ->toArray()],
        ['label' => 'This Month Weekdays', 'dates' => fn () => collect(range(1, now()->daysInMonth))
            ->map(fn ($d) => now()->startOfMonth()->addDays($d - 1))
            ->filter(fn ($d) => $d->isWeekday())
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->toArray()],
        ['label' => 'Specific Dates', 'dates' => ['2026-03-01', '2026-06-15', '2026-12-25']],
    ]),
```

### Callback Hooks

Transform dates before saving or after resolving:

```php
// Filter out weekends before saving
MultipleDatePicker::make('Working Days', 'working_days')
    ->beforeSave(function (array $dates) {
        return collect($dates)
            ->filter(fn ($d) => Carbon::parse($d)->isWeekday())
            ->values()
            ->toArray();
    }),

// Reverse date order when displaying
MultipleDatePicker::make('Dates', 'dates')
    ->afterResolve(fn (array $dates) => array_reverse($dates)),

// JavaScript callback when a date is selected
MultipleDatePicker::make('Dates', 'dates')
    ->onDateSelected('handleDateChange'),
```

### Bulk Actions

Add or remove dates across multiple resources:

```php
// In your Nova Resource actions() method:
use Ghanem\MultipleDatePicker\Actions\BulkAddDates;
use Ghanem\MultipleDatePicker\Actions\BulkRemoveDates;

public function actions(NovaRequest $request)
{
    return [
        new BulkAddDates('dates'),        // attribute name
        new BulkRemoveDates('dates'),
    ];
}
```

### Full Example

```php
MultipleDatePicker::make('Event Dates', 'event_dates')
    ->dateFormat('d/m/Y')
    ->minDate('2026-01-01')
    ->maxDate('2026-12-31')
    ->disabledDates(['2026-12-25'])
    ->minSelections(1)
    ->maxSelections(20)
    ->sorted()
    ->locale('en')
    ->maxDisplayOnIndex(5)
    ->calendarView()
    ->exportFormat('comma')
    ->beforeSave(fn ($dates) => array_unique($dates))
    ->placeholder('Pick event dates'),
```

### Database Migration

The field stores dates as a JSON array:

```php
$table->json('dates')->nullable();
```

Cast it in your model:

```php
protected $casts = [
    'dates' => 'array',
];
```

## Testing

```bash
composer test
```

## License

Nova Multiple DatePicker is open-sourced software licensed under the [MIT license](LICENSE.md).

## Sponsor

[Become a Sponsor](https://github.com/sponsors/AbdullahGhanem)
