<?php

namespace Ghanem\MultipleDatePicker\Tests;

use Carbon\Carbon;
use Ghanem\MultipleDatePicker\MultipleDatePicker;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Orchestra\Testbench\TestCase;
use Illuminate\Http\Request;

class MultipleDatePickerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-03-08 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ─── Instantiation ──────────────────────────────────────────

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');

        $this->assertInstanceOf(MultipleDatePicker::class, $field);
        $this->assertEquals('Dates', $field->name);
        $this->assertEquals('dates', $field->attribute);
    }

    /** @test */
    public function it_has_correct_component_name(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');

        $this->assertEquals('multiple-date-picker', $field->component);
    }

    /** @test */
    public function it_uses_single_attribute_as_name(): void
    {
        $field = MultipleDatePicker::make('Dates');

        $this->assertEquals('Dates', $field->name);
        $this->assertEquals('dates', $field->attribute);
    }

    // ─── fillAttributeFromRequest ────────────────────────────────

    /** @test */
    public function it_fills_attribute_from_request_with_valid_dates(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => '2026-03-01,2026-03-15,2026-03-20',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertIsArray($model->dates);
        $this->assertCount(3, $model->dates);
        $this->assertEquals('2026-03-01', $model->dates[0]);
        $this->assertEquals('2026-03-15', $model->dates[1]);
        $this->assertEquals('2026-03-20', $model->dates[2]);
    }

    /** @test */
    public function it_handles_empty_request_value(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $request = $this->makeNovaRequest(['dates' => '']);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertIsArray($model->dates);
        $this->assertEmpty($model->dates);
    }

    /** @test */
    public function it_handles_false_value(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $request = $this->makeNovaRequest(['dates' => 'false']);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertIsArray($model->dates);
        $this->assertEmpty($model->dates);
    }

    /** @test */
    public function it_filters_out_false_entries_in_mixed_values(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => '2026-03-01,false,2026-03-15',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertCount(2, $model->dates);
        $this->assertEquals('2026-03-01', $model->dates[0]);
        $this->assertEquals('2026-03-15', $model->dates[1]);
    }

    /** @test */
    public function it_removes_duplicate_dates(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => '2026-03-01,2026-03-01,2026-03-15',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertCount(2, $model->dates);
    }

    /** @test */
    public function it_strips_timezone_info_from_dates(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => 'Sun Mar 01 2026 00:00:00 GMT+0300 (Arabian Standard Time),Sun Mar 15 2026 00:00:00 GMT+0300 (Arabian Standard Time)',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertCount(2, $model->dates);
        $this->assertEquals('2026-03-01', $model->dates[0]);
        $this->assertEquals('2026-03-15', $model->dates[1]);
    }

    /** @test */
    public function it_does_not_modify_model_when_attribute_missing_from_request(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();
        $model->dates = ['2026-03-01'];

        $request = $this->makeNovaRequest([]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertEquals(['2026-03-01'], $model->dates);
    }

    /** @test */
    public function it_handles_invalid_dates_gracefully(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => 'not-a-date,2026-03-15,also-invalid',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertCount(1, $model->dates);
        $this->assertEquals('2026-03-15', $model->dates[0]);
    }

    // ─── resolveAttribute ────────────────────────────────────────

    /** @test */
    public function it_resolves_attribute_to_display_format(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');

        $model = new TestModel();
        $model->dates = ['2026-03-01', '2026-03-15', '2026-03-20'];

        $result = $field->resolveAttribute($model, 'dates');

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('01/03/2026', $result[0]);
        $this->assertEquals('15/03/2026', $result[1]);
        $this->assertEquals('20/03/2026', $result[2]);
    }

    /** @test */
    public function it_resolves_empty_attribute(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');

        $model = new TestModel();
        $model->dates = null;

        $result = $field->resolveAttribute($model, 'dates');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_resolves_empty_array_attribute(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');

        $model = new TestModel();
        $model->dates = [];

        $result = $field->resolveAttribute($model, 'dates');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_handles_invalid_dates_in_resolve_gracefully(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');

        $model = new TestModel();
        $model->dates = ['2026-03-01', 'invalid', '2026-03-15'];

        $result = $field->resolveAttribute($model, 'dates');

        $this->assertCount(2, $result);
        $this->assertEquals('01/03/2026', $result[0]);
        $this->assertEquals('15/03/2026', $result[1]);
    }

    // ─── dateFormat ──────────────────────────────────────────────

    /** @test */
    public function it_can_set_custom_date_format(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->dateFormat('Y-m-d');

        $model = new TestModel();
        $model->dates = ['2026-03-01', '2026-03-15'];

        $result = $field->resolveAttribute($model, 'dates');

        $this->assertEquals('2026-03-01', $result[0]);
        $this->assertEquals('2026-03-15', $result[1]);
    }

    /** @test */
    public function it_converts_php_date_format_to_js_format(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->dateFormat('d/m/Y');

        $data = $field->jsonSerialize();
        $this->assertEquals('dd/MM/yyyy', $data['dateFormat']);
    }

    /** @test */
    public function it_converts_day_name_formats(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->dateFormat('D, d M Y');

        $data = $field->jsonSerialize();
        $this->assertEquals('EEE, dd MMM yyyy', $data['dateFormat']);
    }

    /** @test */
    public function it_converts_full_month_name_format(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->dateFormat('F j, Y');

        $data = $field->jsonSerialize();
        $this->assertEquals('MMMM d, yyyy', $data['dateFormat']);
    }

    /** @test */
    public function it_converts_full_day_name_format(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->dateFormat('l, d F Y');

        $data = $field->jsonSerialize();
        $this->assertEquals('EEEE, dd MMMM yyyy', $data['dateFormat']);
    }

    // ─── storageFormat ───────────────────────────────────────────

    /** @test */
    public function it_can_set_custom_storage_format(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->storageFormat('d/m/Y');

        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => '2026-03-01,2026-03-15',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertEquals('01/03/2026', $model->dates[0]);
        $this->assertEquals('15/03/2026', $model->dates[1]);
    }

    // ─── sorted ──────────────────────────────────────────────────

    /** @test */
    public function it_can_sort_dates(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')->sorted();
        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => '2026-03-20,2026-03-01,2026-03-15',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertEquals('2026-03-01', $model->dates[0]);
        $this->assertEquals('2026-03-15', $model->dates[1]);
        $this->assertEquals('2026-03-20', $model->dates[2]);
    }

    /** @test */
    public function it_does_not_sort_by_default(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => '2026-03-20,2026-03-01,2026-03-15',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertEquals('2026-03-20', $model->dates[0]);
        $this->assertEquals('2026-03-01', $model->dates[1]);
        $this->assertEquals('2026-03-15', $model->dates[2]);
    }

    /** @test */
    public function it_can_disable_sorting(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->sorted(true)
            ->sorted(false);

        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => '2026-03-20,2026-03-01',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertEquals('2026-03-20', $model->dates[0]);
        $this->assertEquals('2026-03-01', $model->dates[1]);
    }

    // ─── minDate / maxDate / disabledDates ───────────────────────

    /** @test */
    public function it_can_set_min_date(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->minDate('2026-03-01');

        $data = $field->jsonSerialize();
        $this->assertEquals('2026-03-01', $data['minDate']);
    }

    /** @test */
    public function it_can_set_max_date(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->maxDate('2026-12-31');

        $data = $field->jsonSerialize();
        $this->assertEquals('2026-12-31', $data['maxDate']);
    }

    /** @test */
    public function it_can_set_disabled_dates(): void
    {
        $disabledDates = ['2026-03-05', '2026-03-10'];
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->disabledDates($disabledDates);

        $data = $field->jsonSerialize();
        $this->assertEquals($disabledDates, $data['disabledDates']);
    }

    /** @test */
    public function it_can_set_empty_disabled_dates(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->disabledDates([]);

        $data = $field->jsonSerialize();
        $this->assertEquals([], $data['disabledDates']);
    }

    // ─── maxSelections / minSelections ────────────────────────────

    /** @test */
    public function it_can_set_max_selections(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->maxSelections(5);

        $data = $field->jsonSerialize();
        $this->assertEquals(5, $data['maxSelections']);
    }

    /** @test */
    public function it_can_set_min_selections(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->minSelections(2);

        $data = $field->jsonSerialize();
        $this->assertEquals(2, $data['minSelections']);
    }

    /** @test */
    public function it_can_set_both_min_and_max_selections(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->minSelections(1)
            ->maxSelections(10);

        $data = $field->jsonSerialize();
        $this->assertEquals(1, $data['minSelections']);
        $this->assertEquals(10, $data['maxSelections']);
    }

    // ─── maxDisplayOnIndex ───────────────────────────────────────

    /** @test */
    public function it_can_set_max_display_on_index(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->maxDisplayOnIndex(3);

        $data = $field->jsonSerialize();
        $this->assertEquals(3, $data['maxDisplayOnIndex']);
    }

    // ─── placeholder ─────────────────────────────────────────────

    /** @test */
    public function it_can_set_placeholder(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->placeholder('Select dates...');

        $serialized = $field->jsonSerialize();
        $this->assertEquals(
            'Select dates...',
            $serialized['placeholder'] ?? ($serialized['extraAttributes']['placeholder'] ?? null)
        );
    }

    // ─── v2.1 — Locale ──────────────────────────────────────────

    /** @test */
    public function it_can_set_locale(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->locale('ar');

        $data = $field->jsonSerialize();
        $this->assertEquals('ar', $data['locale']);
    }

    /** @test */
    public function it_can_set_different_locales(): void
    {
        $locales = ['en', 'ar', 'fr', 'de', 'es', 'ja', 'zh'];

        foreach ($locales as $locale) {
            $field = MultipleDatePicker::make('Dates', 'dates')->locale($locale);
            $data = $field->jsonSerialize();
            $this->assertEquals($locale, $data['locale']);
        }
    }

    // ─── v2.1 — Depends-On ──────────────────────────────────────

    /** @test */
    public function it_can_set_min_date_from_field(): void
    {
        $field = MultipleDatePicker::make('End Dates', 'end_dates')
            ->minDateFrom('start_date');

        $data = $field->jsonSerialize();
        $this->assertEquals('start_date', $data['minDateField']);
    }

    /** @test */
    public function it_can_set_max_date_from_field(): void
    {
        $field = MultipleDatePicker::make('Start Dates', 'start_dates')
            ->maxDateFrom('end_date');

        $data = $field->jsonSerialize();
        $this->assertEquals('end_date', $data['maxDateField']);
    }

    /** @test */
    public function it_can_set_both_dependent_fields(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->minDateFrom('start_date')
            ->maxDateFrom('end_date');

        $data = $field->jsonSerialize();
        $this->assertEquals('start_date', $data['minDateField']);
        $this->assertEquals('end_date', $data['maxDateField']);
    }

    // ─── v2.2 — Export Format ────────────────────────────────────

    /** @test */
    public function it_can_set_export_format(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->exportFormat('comma');

        $this->assertInstanceOf(MultipleDatePicker::class, $field);
    }

    /** @test */
    public function it_formats_display_with_comma_separator(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->exportFormat('comma');

        $model = new TestModel();
        $model->dates = ['2026-03-01', '2026-03-15'];

        $field->resolveForDisplay($model);

        $this->assertEquals('01/03/2026, 15/03/2026', $field->displayedAs);
    }

    /** @test */
    public function it_formats_display_with_pipe_separator(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->exportFormat('pipe');

        $model = new TestModel();
        $model->dates = ['2026-03-01', '2026-03-15'];

        $field->resolveForDisplay($model);

        $this->assertEquals('01/03/2026 | 15/03/2026', $field->displayedAs);
    }

    /** @test */
    public function it_formats_display_with_semicolon_separator(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->exportFormat('semicolon');

        $model = new TestModel();
        $model->dates = ['2026-03-01', '2026-03-15'];

        $field->resolveForDisplay($model);

        $this->assertEquals('01/03/2026; 15/03/2026', $field->displayedAs);
    }

    // ─── v2.3 — Range Mode ──────────────────────────────────────

    /** @test */
    public function it_can_enable_range_mode(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->rangeMode();

        $data = $field->jsonSerialize();
        $this->assertTrue($data['rangeMode']);
    }

    /** @test */
    public function it_can_disable_range_mode(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->rangeMode(false);

        $data = $field->jsonSerialize();
        $this->assertFalse($data['rangeMode']);
    }

    /** @test */
    public function it_expands_date_range(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->rangeMode();

        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => '2026-03-01',
            'dates_range_end' => '2026-03-05',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertCount(5, $model->dates);
        $this->assertEquals('2026-03-01', $model->dates[0]);
        $this->assertEquals('2026-03-02', $model->dates[1]);
        $this->assertEquals('2026-03-03', $model->dates[2]);
        $this->assertEquals('2026-03-04', $model->dates[3]);
        $this->assertEquals('2026-03-05', $model->dates[4]);
    }

    // ─── v2.3 — Recurrence ──────────────────────────────────────

    /** @test */
    public function it_can_enable_recurrence(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->allowRecurrence();

        $data = $field->jsonSerialize();
        $this->assertTrue($data['allowRecurrence']);
        $this->assertContains('daily', $data['recurrencePatterns']);
        $this->assertContains('weekly', $data['recurrencePatterns']);
    }

    /** @test */
    public function it_can_set_custom_recurrence_patterns(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->allowRecurrence(['weekly', 'monthly']);

        $data = $field->jsonSerialize();
        $this->assertEquals(['weekly', 'monthly'], $data['recurrencePatterns']);
    }

    /** @test */
    public function it_expands_daily_recurrence(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->allowRecurrence();

        $dates = $field->expandRecurrence('2026-03-01', '2026-03-05', 'daily');

        $this->assertCount(5, $dates);
        $this->assertEquals('2026-03-01', $dates[0]);
        $this->assertEquals('2026-03-05', $dates[4]);
    }

    /** @test */
    public function it_expands_weekly_recurrence(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->allowRecurrence();

        $dates = $field->expandRecurrence('2026-03-01', '2026-03-29', 'weekly');

        $this->assertCount(5, $dates);
        $this->assertEquals('2026-03-01', $dates[0]);
        $this->assertEquals('2026-03-08', $dates[1]);
        $this->assertEquals('2026-03-15', $dates[2]);
        $this->assertEquals('2026-03-22', $dates[3]);
        $this->assertEquals('2026-03-29', $dates[4]);
    }

    /** @test */
    public function it_expands_biweekly_recurrence(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->allowRecurrence();

        $dates = $field->expandRecurrence('2026-03-01', '2026-03-29', 'biweekly');

        $this->assertCount(3, $dates);
        $this->assertEquals('2026-03-01', $dates[0]);
        $this->assertEquals('2026-03-15', $dates[1]);
        $this->assertEquals('2026-03-29', $dates[2]);
    }

    /** @test */
    public function it_expands_monthly_recurrence(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->allowRecurrence();

        $dates = $field->expandRecurrence('2026-01-15', '2026-04-15', 'monthly');

        $this->assertCount(4, $dates);
        $this->assertEquals('2026-01-15', $dates[0]);
        $this->assertEquals('2026-02-15', $dates[1]);
        $this->assertEquals('2026-03-15', $dates[2]);
        $this->assertEquals('2026-04-15', $dates[3]);
    }

    /** @test */
    public function it_expands_weekdays_recurrence(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->allowRecurrence();

        // 2026-03-02 is Monday, 2026-03-06 is Friday
        $dates = $field->expandRecurrence('2026-03-02', '2026-03-08', 'weekdays');

        $this->assertCount(5, $dates);
        $this->assertEquals('2026-03-02', $dates[0]); // Mon
        $this->assertEquals('2026-03-06', $dates[4]); // Fri
    }

    /** @test */
    public function it_expands_weekends_recurrence(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->allowRecurrence();

        // 2026-03-01 is Sunday, 2026-03-08 is Sunday
        $dates = $field->expandRecurrence('2026-03-01', '2026-03-08', 'weekends');

        $this->assertCount(3, $dates);
        $this->assertEquals('2026-03-01', $dates[0]); // Sun
        $this->assertEquals('2026-03-07', $dates[1]); // Sat
        $this->assertEquals('2026-03-08', $dates[2]); // Sun
    }

    /** @test */
    public function it_fills_with_recurrence_from_request(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->allowRecurrence();

        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => '2026-03-01',
            'dates_recurrence_end' => '2026-03-07',
            'dates_recurrence' => 'daily',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertCount(7, $model->dates);
    }

    // ─── v2.4 — Calendar View ───────────────────────────────────

    /** @test */
    public function it_can_enable_calendar_view(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->calendarView();

        $data = $field->jsonSerialize();
        $this->assertTrue($data['calendarView']);
    }

    /** @test */
    public function it_can_disable_calendar_view(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->calendarView(false);

        $data = $field->jsonSerialize();
        $this->assertFalse($data['calendarView']);
    }

    // ─── v2.4 — Presets ─────────────────────────────────────────

    /** @test */
    public function it_can_set_presets_with_arrays(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->presets([
                ['label' => 'This Week', 'dates' => ['2026-03-08', '2026-03-09', '2026-03-10']],
            ]);

        $data = $field->jsonSerialize();
        $this->assertCount(1, $data['presets']);
        $this->assertEquals('This Week', $data['presets'][0]['label']);
        $this->assertCount(3, $data['presets'][0]['dates']);
    }

    /** @test */
    public function it_can_set_presets_with_callables(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->presets([
                [
                    'label' => 'Next 3 Days',
                    'dates' => fn () => ['2026-03-09', '2026-03-10', '2026-03-11'],
                ],
            ]);

        $data = $field->jsonSerialize();
        $this->assertCount(1, $data['presets']);
        $this->assertEquals('Next 3 Days', $data['presets'][0]['label']);
        $this->assertCount(3, $data['presets'][0]['dates']);
        $this->assertEquals('2026-03-09', $data['presets'][0]['dates'][0]);
    }

    /** @test */
    public function it_can_set_multiple_presets(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->presets([
                ['label' => 'Preset A', 'dates' => ['2026-03-01']],
                ['label' => 'Preset B', 'dates' => ['2026-03-15']],
                ['label' => 'Preset C', 'dates' => fn () => ['2026-03-20', '2026-03-21']],
            ]);

        $data = $field->jsonSerialize();
        $this->assertCount(3, $data['presets']);
    }

    // ─── v3.0 — Callback Hooks ──────────────────────────────────

    /** @test */
    public function it_can_set_before_save_callback(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->beforeSave(fn (array $dates) => array_map('strtoupper', $dates));

        $this->assertInstanceOf(MultipleDatePicker::class, $field);
    }

    /** @test */
    public function it_runs_before_save_callback(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->beforeSave(function (array $dates) {
                // Filter out weekends
                return collect($dates)->filter(function ($d) {
                    return Carbon::parse($d)->isWeekday();
                })->values()->toArray();
            });

        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => '2026-03-02,2026-03-07,2026-03-08,2026-03-09',
            // Mon, Sat, Sun, Mon
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        // Only weekdays should remain: 2026-03-02 (Mon) and 2026-03-09 (Mon)
        $this->assertCount(2, $model->dates);
        $this->assertEquals('2026-03-02', $model->dates[0]);
        $this->assertEquals('2026-03-09', $model->dates[1]);
    }

    /** @test */
    public function it_can_set_after_resolve_callback(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->afterResolve(fn (array $dates) => array_reverse($dates));

        $this->assertInstanceOf(MultipleDatePicker::class, $field);
    }

    /** @test */
    public function it_runs_after_resolve_callback(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->afterResolve(fn (array $dates) => array_reverse($dates));

        $model = new TestModel();
        $model->dates = ['2026-03-01', '2026-03-15'];

        $result = $field->resolveAttribute($model, 'dates');

        $this->assertEquals('15/03/2026', $result[0]);
        $this->assertEquals('01/03/2026', $result[1]);
    }

    /** @test */
    public function it_can_set_on_date_selected_js_callback(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->onDateSelected('handleDateChange');

        $data = $field->jsonSerialize();
        $this->assertEquals('handleDateChange', $data['onDateSelected']);
    }

    // ─── Chaining ────────────────────────────────────────────────

    /** @test */
    public function it_can_chain_all_options(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates')
            ->dateFormat('Y-m-d')
            ->storageFormat('Y-m-d')
            ->minDate('2026-01-01')
            ->maxDate('2026-12-31')
            ->disabledDates(['2026-06-15'])
            ->sorted()
            ->maxSelections(10)
            ->minSelections(1)
            ->maxDisplayOnIndex(5)
            ->locale('ar')
            ->calendarView()
            ->placeholder('Pick dates');

        $this->assertInstanceOf(MultipleDatePicker::class, $field);

        $data = $field->jsonSerialize();
        $this->assertEquals('2026-01-01', $data['minDate']);
        $this->assertEquals('2026-12-31', $data['maxDate']);
        $this->assertEquals(['2026-06-15'], $data['disabledDates']);
        $this->assertEquals(10, $data['maxSelections']);
        $this->assertEquals(1, $data['minSelections']);
        $this->assertEquals(5, $data['maxDisplayOnIndex']);
        $this->assertEquals('ar', $data['locale']);
        $this->assertTrue($data['calendarView']);
    }

    /** @test */
    public function it_returns_static_from_all_setters(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');

        $this->assertSame($field, $field->dateFormat('Y-m-d'));
        $this->assertSame($field, $field->storageFormat('Y-m-d'));
        $this->assertSame($field, $field->minDate('2026-01-01'));
        $this->assertSame($field, $field->maxDate('2026-12-31'));
        $this->assertSame($field, $field->disabledDates([]));
        $this->assertSame($field, $field->sorted());
        $this->assertSame($field, $field->maxSelections(5));
        $this->assertSame($field, $field->minSelections(1));
        $this->assertSame($field, $field->maxDisplayOnIndex(3));
        $this->assertSame($field, $field->locale('en'));
        $this->assertSame($field, $field->minDateFrom('start'));
        $this->assertSame($field, $field->maxDateFrom('end'));
        $this->assertSame($field, $field->exportFormat('comma'));
        $this->assertSame($field, $field->rangeMode());
        $this->assertSame($field, $field->allowRecurrence());
        $this->assertSame($field, $field->calendarView());
        $this->assertSame($field, $field->presets([['label' => 'X', 'dates' => []]]));
        $this->assertSame($field, $field->beforeSave(fn ($d) => $d));
        $this->assertSame($field, $field->afterResolve(fn ($d) => $d));
        $this->assertSame($field, $field->onDateSelected('fn'));
    }

    // ─── Edge cases ──────────────────────────────────────────────

    /** @test */
    public function it_handles_single_date(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $request = $this->makeNovaRequest(['dates' => '2026-03-15']);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertCount(1, $model->dates);
        $this->assertEquals('2026-03-15', $model->dates[0]);
    }

    /** @test */
    public function it_handles_dates_with_whitespace(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $request = $this->makeNovaRequest([
            'dates' => ' 2026-03-01 , 2026-03-15 ',
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertCount(2, $model->dates);
        $this->assertEquals('2026-03-01', $model->dates[0]);
        $this->assertEquals('2026-03-15', $model->dates[1]);
    }

    /** @test */
    public function it_resolves_single_date(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');

        $model = new TestModel();
        $model->dates = ['2026-12-25'];

        $result = $field->resolveAttribute($model, 'dates');

        $this->assertCount(1, $result);
        $this->assertEquals('25/12/2026', $result[0]);
    }

    /** @test */
    public function it_handles_many_dates(): void
    {
        $field = MultipleDatePicker::make('Dates', 'dates');
        $model = new TestModel();

        $dates = [];
        for ($i = 1; $i <= 30; $i++) {
            $dates[] = sprintf('2026-03-%02d', $i);
        }

        $request = $this->makeNovaRequest([
            'dates' => implode(',', $dates),
        ]);

        $this->invokeMethod($field, 'fillAttributeFromRequest', [
            $request, 'dates', $model, 'dates',
        ]);

        $this->assertCount(30, $model->dates);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    protected function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    protected function makeNovaRequest(array $data): NovaRequest
    {
        $request = Request::create('/', 'POST', $data);

        return NovaRequest::createFrom($request);
    }
}

class TestModel extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'dates' => 'array',
    ];
}
