<?php

namespace Ghanem\MultipleDatePicker;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Closure;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class MultipleDatePicker extends Field
{
    /**
     * The field's component.
     */
    public $component = 'multiple-date-picker';

    /**
     * The date format for display.
     */
    protected string $dateFormat = 'd/m/Y';

    /**
     * The date format for storage.
     */
    protected string $storageFormat = 'Y-m-d';

    /**
     * Whether to sort dates.
     */
    protected bool $shouldSort = false;

    /**
     * Whether range mode is enabled.
     */
    protected bool $rangeMode = false;

    /**
     * Recurrence pattern configuration.
     */
    protected ?array $recurrenceConfig = null;

    /**
     * The export format.
     */
    protected ?string $exportFormat = null;

    /**
     * Callback to run before saving.
     */
    protected ?Closure $beforeSaveCallback = null;

    /**
     * Callback to run after resolving.
     */
    protected ?Closure $afterResolveCallback = null;

    /**
     * Presets configuration.
     */
    protected array $presets = [];

    // ─── v1.x — Display & Format ─────────────────────────────────

    /**
     * Set the display date format.
     */
    public function dateFormat(string $format): static
    {
        $this->dateFormat = $format;

        return $this->withMeta(['dateFormat' => $this->convertToJsFormat($format)]);
    }

    /**
     * Set the storage date format.
     */
    public function storageFormat(string $format): static
    {
        $this->storageFormat = $format;

        return $this;
    }

    /**
     * Set the minimum selectable date.
     */
    public function minDate(string $date): static
    {
        return $this->withMeta(['minDate' => $date]);
    }

    /**
     * Set the maximum selectable date.
     */
    public function maxDate(string $date): static
    {
        return $this->withMeta(['maxDate' => $date]);
    }

    /**
     * Set specific dates that should be disabled.
     */
    public function disabledDates(array $dates): static
    {
        return $this->withMeta(['disabledDates' => $dates]);
    }

    /**
     * Sort dates in ascending order.
     */
    public function sorted(bool $sorted = true): static
    {
        $this->shouldSort = $sorted;

        return $this;
    }

    /**
     * Set the maximum number of dates that can be selected.
     */
    public function maxSelections(int $max): static
    {
        return $this->withMeta(['maxSelections' => $max]);
    }

    /**
     * Set the minimum number of dates that must be selected.
     */
    public function minSelections(int $min): static
    {
        return $this->withMeta(['minSelections' => $min]);
    }

    /**
     * Set the maximum number of dates to display on the index view.
     */
    public function maxDisplayOnIndex(int $count): static
    {
        return $this->withMeta(['maxDisplayOnIndex' => $count]);
    }

    // ─── v2.1 — Locale & Depends-On ─────────────────────────────

    /**
     * Set the locale for the date picker.
     */
    public function locale(string $locale): static
    {
        return $this->withMeta(['locale' => $locale]);
    }

    /**
     * Set the field that controls the minimum date (depends-on).
     */
    public function minDateFrom(string $fieldAttribute): static
    {
        return $this->withMeta(['minDateField' => $fieldAttribute]);
    }

    /**
     * Set the field that controls the maximum date (depends-on).
     */
    public function maxDateFrom(string $fieldAttribute): static
    {
        return $this->withMeta(['maxDateField' => $fieldAttribute]);
    }

    // ─── v2.2 — Export Format ────────────────────────────────────

    /**
     * Set the export format for CSV exports.
     *
     * Supported: 'comma', 'newline', 'pipe', 'semicolon'
     */
    public function exportFormat(string $format): static
    {
        $this->exportFormat = $format;

        return $this;
    }

    /**
     * Resolve the field's value for display.
     */
    public function resolveForDisplay($resource, $attribute = null)
    {
        parent::resolveForDisplay($resource, $attribute);

        if ($this->exportFormat && is_array($this->value)) {
            $separator = match ($this->exportFormat) {
                'comma' => ', ',
                'newline' => "\n",
                'pipe' => ' | ',
                'semicolon' => '; ',
                default => ', ',
            };

            $this->displayedAs = implode($separator, $this->value);
        }
    }

    // ─── v2.3 — Range Mode & Recurrence ─────────────────────────

    /**
     * Enable range mode — user picks start and end, all dates in between are stored.
     */
    public function rangeMode(bool $enabled = true): static
    {
        $this->rangeMode = $enabled;

        return $this->withMeta(['rangeMode' => $enabled]);
    }

    /**
     * Enable recurrence pattern selection.
     *
     * Supported patterns: 'daily', 'weekly', 'biweekly', 'monthly', 'weekdays', 'weekends'
     */
    public function allowRecurrence(array $patterns = ['daily', 'weekly', 'biweekly', 'monthly', 'weekdays', 'weekends']): static
    {
        $this->recurrenceConfig = $patterns;

        return $this->withMeta(['allowRecurrence' => true, 'recurrencePatterns' => $patterns]);
    }

    /**
     * Expand a date range into individual dates.
     */
    protected function expandDateRange(string $startDate, string $endDate): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);

        return collect($period)
            ->map(fn (Carbon $date) => $date->format($this->storageFormat))
            ->toArray();
    }

    /**
     * Expand a recurrence pattern into individual dates.
     */
    public function expandRecurrence(string $startDate, string $endDate, string $pattern): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $dates = [];

        $current = $start->copy();

        while ($current->lte($end)) {
            $shouldInclude = match ($pattern) {
                'daily' => true,
                'weekly' => true,
                'biweekly' => true,
                'monthly' => true,
                'weekdays' => $current->isWeekday(),
                'weekends' => $current->isWeekend(),
                default => true,
            };

            if ($shouldInclude) {
                $dates[] = $current->format($this->storageFormat);
            }

            $current = match ($pattern) {
                'weekly' => $current->addWeek(),
                'biweekly' => $current->addWeeks(2),
                'monthly' => $current->addMonth(),
                default => $current->addDay(),
            };
        }

        return $dates;
    }

    // ─── v2.4 — Calendar View & Presets ─────────────────────────

    /**
     * Enable calendar heatmap view on the detail page.
     */
    public function calendarView(bool $enabled = true): static
    {
        return $this->withMeta(['calendarView' => $enabled]);
    }

    /**
     * Set predefined date presets for quick selection.
     *
     * Each preset is ['label' => string, 'dates' => callable|array]
     */
    public function presets(array $presets): static
    {
        $this->presets = $presets;

        $resolvedPresets = collect($presets)->map(function ($preset) {
            $dates = is_callable($preset['dates']) ? ($preset['dates'])() : $preset['dates'];

            return [
                'label' => $preset['label'],
                'dates' => collect($dates)->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->toArray(),
            ];
        })->toArray();

        return $this->withMeta(['presets' => $resolvedPresets]);
    }

    // ─── v3.0 — Callback Hooks ──────────────────────────────────

    /**
     * Register a callback to transform dates before saving.
     */
    public function beforeSave(Closure $callback): static
    {
        $this->beforeSaveCallback = $callback;

        return $this;
    }

    /**
     * Register a callback to transform dates after resolving.
     */
    public function afterResolve(Closure $callback): static
    {
        $this->afterResolveCallback = $callback;

        return $this;
    }

    /**
     * Register a JavaScript callback name to fire when a date is selected.
     */
    public function onDateSelected(string $jsCallback): static
    {
        return $this->withMeta(['onDateSelected' => $jsCallback]);
    }

    // ─── Core Logic ─────────────────────────────────────────────

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     */
    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ): void {
        if ($request->exists($requestAttribute)) {
            $rawValue = $request[$requestAttribute];

            if (empty($rawValue) || $rawValue === 'false') {
                $model->{$attribute} = [];
                return;
            }

            // Handle range mode
            if ($this->rangeMode && $request->has($requestAttribute.'_range_end')) {
                $startDate = trim($rawValue);
                $endDate = trim($request[$requestAttribute.'_range_end']);

                try {
                    $items = $this->expandDateRange($startDate, $endDate);
                    $model->{$attribute} = $items;
                    return;
                } catch (\Exception) {
                    // Fall through to normal processing
                }
            }

            // Handle recurrence
            if ($this->recurrenceConfig && $request->has($requestAttribute.'_recurrence')) {
                $pattern = $request[$requestAttribute.'_recurrence'];
                $startDate = trim($rawValue);
                $endDate = trim($request[$requestAttribute.'_recurrence_end'] ?? $rawValue);

                try {
                    $items = $this->expandRecurrence($startDate, $endDate, $pattern);
                    $model->{$attribute} = $items;
                    return;
                } catch (\Exception) {
                    // Fall through to normal processing
                }
            }

            $items = collect(explode(',', $rawValue))
                ->filter(fn ($i) => ! empty($i) && $i !== 'false')
                ->map(function ($i) {
                    $dateString = trim(preg_replace('/\(.*$/', '', $i));

                    try {
                        return Carbon::parse($dateString)->format($this->storageFormat);
                    } catch (\Exception) {
                        return null;
                    }
                })
                ->filter()
                ->unique()
                ->values();

            if ($this->shouldSort) {
                $items = $items->sort()->values();
            }

            $dates = $items->toArray();

            if ($this->beforeSaveCallback) {
                $dates = ($this->beforeSaveCallback)($dates);
            }

            $model->{$attribute} = $dates;
        }
    }

    /**
     * Resolve the given attribute from the given resource.
     */
    public function resolveAttribute($resource, $attribute): mixed
    {
        $value = $resource->{$attribute};

        if (empty($value)) {
            return [];
        }

        $items = collect($value)
            ->filter()
            ->map(function ($i) {
                try {
                    return Carbon::parse($i)->format($this->dateFormat);
                } catch (\Exception) {
                    return null;
                }
            })
            ->filter()
            ->values()
            ->toArray();

        if ($this->afterResolveCallback) {
            $items = ($this->afterResolveCallback)($items);
        }

        return $items;
    }

    /**
     * Convert PHP date format to JavaScript date format.
     */
    protected function convertToJsFormat(string $phpFormat): string
    {
        $result = '';
        $length = strlen($phpFormat);

        for ($i = 0; $i < $length; $i++) {
            $result .= match ($phpFormat[$i]) {
                'd' => 'dd',
                'j' => 'd',
                'D' => 'EEE',
                'l' => 'EEEE',
                'm' => 'MM',
                'n' => 'M',
                'M' => 'MMM',
                'F' => 'MMMM',
                'Y' => 'yyyy',
                'y' => 'yy',
                'H' => 'HH',
                'h' => 'hh',
                'G' => 'H',
                'g' => 'h',
                'i' => 'mm',
                's' => 'ss',
                'A' => 'a',
                'a' => 'a',
                default => $phpFormat[$i],
            };
        }

        return $result;
    }
}
