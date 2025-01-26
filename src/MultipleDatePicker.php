<?php

namespace Ghanem\MultipleDatePicker;

use Carbon\Carbon;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class MultipleDatePicker extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'multiple-date-picker';


    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ) {
        if ($request->exists($requestAttribute)) {
            $items = collect(explode(',', $request[$requestAttribute]))->map(function ($i) {
                if ($i != 'false') {
                    $dateString = preg_replace('/\(.*$/', '', $i);
                    return Carbon::parse($dateString)->isoFormat('YYYY-MM-DD') ?? null;
                }
            })->filter();
            $model->{$attribute} = $items;
        }
    }

    public function resolveAttribute($resource, $attribute): mixed
    {
        $items = collect($resource->{$attribute})->map(function ($i) {
            return Carbon::parse($i)->format('d/m/Y') ?? null;
        })->filter();
        // dd($items);
        return $items;
    }
}
