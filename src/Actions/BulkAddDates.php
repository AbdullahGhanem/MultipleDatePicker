<?php

namespace Ghanem\MultipleDatePicker\Actions;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class BulkAddDates extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The attribute name where dates are stored.
     */
    protected string $dateAttribute = 'dates';

    /**
     * The storage format for dates.
     */
    protected string $storageFormat = 'Y-m-d';

    public $name = 'Add Dates';

    /**
     * Create a new action instance.
     */
    public function __construct(string $dateAttribute = 'dates', string $storageFormat = 'Y-m-d')
    {
        $this->dateAttribute = $dateAttribute;
        $this->storageFormat = $storageFormat;
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        $datesToAdd = collect(explode(',', $fields->get('dates')))
            ->map(fn ($d) => trim($d))
            ->filter()
            ->map(function ($d) {
                try {
                    return Carbon::parse($d)->format($this->storageFormat);
                } catch (\Exception) {
                    return null;
                }
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($datesToAdd)) {
            return Action::danger('No valid dates provided.');
        }

        $count = 0;

        foreach ($models as $model) {
            $existing = $model->{$this->dateAttribute} ?? [];

            if (! is_array($existing)) {
                $existing = [];
            }

            $merged = collect(array_merge($existing, $datesToAdd))
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            $model->{$this->dateAttribute} = $merged;
            $model->save();
            $count++;
        }

        return Action::message("Added dates to {$count} resource(s).");
    }

    /**
     * Get the fields available on the action.
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Dates')
                ->help('Comma-separated dates (e.g., 2026-03-01, 2026-03-15)')
                ->rules('required'),
        ];
    }
}
