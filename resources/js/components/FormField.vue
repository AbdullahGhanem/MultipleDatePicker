<template>
    <DefaultField
        :field="field"
        :errors="errors"
        :show-help-text="showHelpText"
    >
        <template #field>
            <div class="multiple-date-picker-wrapper" :class="{ 'has-error': hasError }">
                <!-- Presets dropdown -->
                <div v-if="field.presets && field.presets.length" class="mdp-presets">
                    <select
                        class="mdp-preset-select"
                        @change="applyPreset($event)"
                        :disabled="isReadonly"
                    >
                        <option value="">Quick select...</option>
                        <option
                            v-for="(preset, idx) in field.presets"
                            :key="idx"
                            :value="idx"
                        >
                            {{ preset.label }}
                        </option>
                    </select>
                </div>

                <!-- Range mode -->
                <div v-if="field.rangeMode" class="mdp-range">
                    <div class="mdp-range-row">
                        <VDatePicker
                            v-model="rangeStart"
                            mode="date"
                            :is-dark="isDarkMode"
                            :locale="field.locale || undefined"
                            :min-date="effectiveMinDate"
                            :max-date="effectiveMaxDate"
                            :disabled-dates="parsedDisabledDates"
                            color="blue"
                        >
                            <template #default="{ togglePopover }">
                                <div
                                    class="mdp-input-container mdp-range-input"
                                    :class="{ 'mdp-disabled': isReadonly }"
                                    @click="!isReadonly && togglePopover()"
                                >
                                    <span v-if="rangeStart" class="mdp-tag">{{ formatDate(rangeStart) }}</span>
                                    <span v-else class="mdp-placeholder">Start date</span>
                                </div>
                            </template>
                        </VDatePicker>
                        <span class="mdp-range-separator">to</span>
                        <VDatePicker
                            v-model="rangeEnd"
                            mode="date"
                            :is-dark="isDarkMode"
                            :locale="field.locale || undefined"
                            :min-date="rangeStart || effectiveMinDate"
                            :max-date="effectiveMaxDate"
                            :disabled-dates="parsedDisabledDates"
                            color="blue"
                        >
                            <template #default="{ togglePopover }">
                                <div
                                    class="mdp-input-container mdp-range-input"
                                    :class="{ 'mdp-disabled': isReadonly }"
                                    @click="!isReadonly && togglePopover()"
                                >
                                    <span v-if="rangeEnd" class="mdp-tag">{{ formatDate(rangeEnd) }}</span>
                                    <span v-else class="mdp-placeholder">End date</span>
                                </div>
                            </template>
                        </VDatePicker>
                    </div>
                    <p v-if="rangeDateCount > 0" class="mdp-hint">
                        {{ rangeDateCount }} date(s) selected
                    </p>
                </div>

                <!-- Recurrence mode -->
                <div v-else-if="field.allowRecurrence" class="mdp-recurrence">
                    <div class="mdp-recurrence-row">
                        <VDatePicker
                            v-model="recurrenceStart"
                            mode="date"
                            :is-dark="isDarkMode"
                            :locale="field.locale || undefined"
                            :min-date="effectiveMinDate"
                            :max-date="effectiveMaxDate"
                            color="blue"
                        >
                            <template #default="{ togglePopover }">
                                <div
                                    class="mdp-input-container mdp-range-input"
                                    :class="{ 'mdp-disabled': isReadonly }"
                                    @click="!isReadonly && togglePopover()"
                                >
                                    <span v-if="recurrenceStart" class="mdp-tag">{{ formatDate(recurrenceStart) }}</span>
                                    <span v-else class="mdp-placeholder">Start date</span>
                                </div>
                            </template>
                        </VDatePicker>
                        <span class="mdp-range-separator">to</span>
                        <VDatePicker
                            v-model="recurrenceEnd"
                            mode="date"
                            :is-dark="isDarkMode"
                            :locale="field.locale || undefined"
                            :min-date="recurrenceStart || effectiveMinDate"
                            :max-date="effectiveMaxDate"
                            color="blue"
                        >
                            <template #default="{ togglePopover }">
                                <div
                                    class="mdp-input-container mdp-range-input"
                                    :class="{ 'mdp-disabled': isReadonly }"
                                    @click="!isReadonly && togglePopover()"
                                >
                                    <span v-if="recurrenceEnd" class="mdp-tag">{{ formatDate(recurrenceEnd) }}</span>
                                    <span v-else class="mdp-placeholder">End date</span>
                                </div>
                            </template>
                        </VDatePicker>
                    </div>
                    <div class="mdp-recurrence-pattern">
                        <select
                            v-model="recurrencePattern"
                            class="mdp-preset-select"
                            :disabled="isReadonly"
                        >
                            <option value="">Select pattern...</option>
                            <option
                                v-for="p in field.recurrencePatterns"
                                :key="p"
                                :value="p"
                            >
                                {{ patternLabel(p) }}
                            </option>
                        </select>
                    </div>
                    <p v-if="recurrenceDateCount > 0" class="mdp-hint">
                        {{ recurrenceDateCount }} date(s) will be generated
                    </p>
                </div>

                <!-- Normal multi-date picker -->
                <template v-else>
                    <VDatePicker
                        v-model="selectedDates"
                        mode="date"
                        :is-dark="isDarkMode"
                        :locale="field.locale || undefined"
                        :min-date="effectiveMinDate"
                        :max-date="effectiveMaxDate"
                        :disabled-dates="parsedDisabledDates"
                        :is-required="field.required"
                        color="blue"
                    >
                        <template #default="{ togglePopover }">
                            <div
                                class="mdp-input-container"
                                :class="{ 'mdp-disabled': isReadonly }"
                                @click="!isReadonly && togglePopover()"
                            >
                                <div v-if="selectedDates.length" class="mdp-tags">
                                    <span
                                        v-for="(date, idx) in selectedDates"
                                        :key="idx"
                                        class="mdp-tag"
                                    >
                                        {{ formatDate(date) }}
                                        <button
                                            v-if="!isReadonly"
                                            type="button"
                                            class="mdp-tag-remove"
                                            @click.stop="removeDate(idx)"
                                        >
                                            &times;
                                        </button>
                                    </span>
                                    <span
                                        v-if="field.maxSelections && selectedDates.length >= field.maxSelections"
                                        class="mdp-limit-badge"
                                    >
                                        max reached
                                    </span>
                                </div>
                                <span v-else class="mdp-placeholder">
                                    {{ field.placeholder || field.name }}
                                </span>
                            </div>
                        </template>
                    </VDatePicker>
                </template>

                <p
                    v-if="field.minSelections && selectedDates.length < field.minSelections && !field.rangeMode && !field.allowRecurrence"
                    class="mdp-hint"
                >
                    Select at least {{ field.minSelections }} date(s)
                </p>
            </div>
        </template>
    </DefaultField>
</template>

<script>
import { DatePicker as VDatePicker } from "v-calendar";

export default {
    components: { VDatePicker },

    mixins: [
        ...(window.Nova ? [Nova.mixins.FormField, Nova.mixins.HandlesValidationErrors] : []),
    ],

    props: ["resourceName", "resourceId", "field"],

    data() {
        return {
            selectedDates: [],
            // Range mode
            rangeStart: null,
            rangeEnd: null,
            // Recurrence mode
            recurrenceStart: null,
            recurrenceEnd: null,
            recurrencePattern: "",
            // Depends-on values
            dependentMinDate: null,
            dependentMaxDate: null,
        };
    },

    computed: {
        hasError() {
            return this.errors && this.errors.has && this.errors.has(this.field.attribute);
        },

        isReadonly() {
            return this.field.readonly || this.field.disabled || false;
        },

        isDarkMode() {
            return document.documentElement.classList.contains("dark");
        },

        effectiveMinDate() {
            return this.dependentMinDate || this.field.minDate || undefined;
        },

        effectiveMaxDate() {
            return this.dependentMaxDate || this.field.maxDate || undefined;
        },

        parsedDisabledDates() {
            if (!this.field.disabledDates) return undefined;
            return this.field.disabledDates.map((d) => new Date(d + "T00:00:00"));
        },

        rangeDateCount() {
            if (!this.rangeStart || !this.rangeEnd) return 0;
            const start = new Date(this.rangeStart);
            const end = new Date(this.rangeEnd);
            const diff = Math.abs(end - start);
            return Math.floor(diff / (1000 * 60 * 60 * 24)) + 1;
        },

        recurrenceDateCount() {
            if (!this.recurrenceStart || !this.recurrenceEnd || !this.recurrencePattern) return 0;
            const dates = this.generateRecurrenceDates();
            return dates.length;
        },
    },

    watch: {
        selectedDates: {
            handler(dates) {
                if (this.field.maxSelections && dates.length > this.field.maxSelections) {
                    this.selectedDates = dates.slice(0, this.field.maxSelections);
                    return;
                }
                this.value = dates
                    .map((d) => this.toISODate(d))
                    .filter(Boolean)
                    .join(",");

                if (this.field.onDateSelected && window[this.field.onDateSelected]) {
                    window[this.field.onDateSelected](dates, this.field.attribute);
                }
            },
            deep: true,
        },

        rangeStart() { this.updateRangeValue(); },
        rangeEnd() { this.updateRangeValue(); },
        recurrenceStart() { this.updateRecurrenceValue(); },
        recurrenceEnd() { this.updateRecurrenceValue(); },
        recurrencePattern() { this.updateRecurrenceValue(); },
    },

    mounted() {
        this.setupDependsOn();
    },

    methods: {
        setInitialValue() {
            const rawValue = this.field.value;
            if (!rawValue || (Array.isArray(rawValue) && rawValue.length === 0)) {
                this.selectedDates = [];
                this.value = "";
                return;
            }

            if (Array.isArray(rawValue)) {
                this.selectedDates = rawValue
                    .map((d) => this.parseDisplayDate(d))
                    .filter(Boolean);
            } else if (typeof rawValue === "string" && rawValue.length > 0) {
                this.selectedDates = rawValue
                    .split(",")
                    .map((d) => this.parseDisplayDate(d.trim()))
                    .filter(Boolean);
            } else {
                this.selectedDates = [];
            }

            this.value = this.selectedDates
                .map((d) => this.toISODate(d))
                .filter(Boolean)
                .join(",");
        },

        fill(formData) {
            if (this.field.rangeMode) {
                formData.append(this.field.attribute, this.toISODate(this.rangeStart) || "");
                formData.append(this.field.attribute + "_range_end", this.toISODate(this.rangeEnd) || "");
            } else if (this.field.allowRecurrence) {
                formData.append(this.field.attribute, this.toISODate(this.recurrenceStart) || "");
                formData.append(this.field.attribute + "_recurrence_end", this.toISODate(this.recurrenceEnd) || "");
                formData.append(this.field.attribute + "_recurrence", this.recurrencePattern || "");
            } else {
                const dates = this.selectedDates
                    .map((d) => this.toISODate(d))
                    .filter(Boolean)
                    .join(",");
                formData.append(this.field.attribute, dates || "");
            }
        },

        removeDate(index) {
            this.selectedDates.splice(index, 1);
        },

        applyPreset(event) {
            const idx = event.target.value;
            if (idx === "" || !this.field.presets[idx]) return;

            const preset = this.field.presets[idx];
            this.selectedDates = preset.dates
                .map((d) => new Date(d + "T00:00:00"))
                .filter((d) => !isNaN(d.getTime()));

            event.target.value = "";
        },

        updateRangeValue() {
            if (this.rangeStart && this.rangeEnd) {
                this.value = this.toISODate(this.rangeStart) + "," + this.toISODate(this.rangeEnd);
            }
        },

        updateRecurrenceValue() {
            if (this.recurrenceStart && this.recurrenceEnd && this.recurrencePattern) {
                const dates = this.generateRecurrenceDates();
                this.value = dates.join(",");
            }
        },

        generateRecurrenceDates() {
            if (!this.recurrenceStart || !this.recurrenceEnd || !this.recurrencePattern) return [];

            const start = new Date(this.recurrenceStart);
            const end = new Date(this.recurrenceEnd);
            const dates = [];
            const current = new Date(start);

            while (current <= end) {
                const dayOfWeek = current.getDay();
                let include = true;

                if (this.recurrencePattern === "weekdays") {
                    include = dayOfWeek >= 1 && dayOfWeek <= 5;
                } else if (this.recurrencePattern === "weekends") {
                    include = dayOfWeek === 0 || dayOfWeek === 6;
                }

                if (include) {
                    dates.push(this.toISODate(new Date(current)));
                }

                switch (this.recurrencePattern) {
                    case "weekly":
                        current.setDate(current.getDate() + 7);
                        break;
                    case "biweekly":
                        current.setDate(current.getDate() + 14);
                        break;
                    case "monthly":
                        current.setMonth(current.getMonth() + 1);
                        break;
                    default:
                        current.setDate(current.getDate() + 1);
                        break;
                }
            }

            return dates.filter(Boolean);
        },

        patternLabel(pattern) {
            const labels = {
                daily: "Every day",
                weekly: "Every week",
                biweekly: "Every 2 weeks",
                monthly: "Every month",
                weekdays: "Weekdays only",
                weekends: "Weekends only",
            };
            return labels[pattern] || pattern;
        },

        setupDependsOn() {
            if (!this.field.minDateField && !this.field.maxDateField) return;

            const form = this.$el?.closest("form");
            if (!form) return;

            const observer = new MutationObserver(() => {
                if (this.field.minDateField) {
                    const el = form.querySelector(`[dusk="${this.field.minDateField}"]`);
                    if (el && el.value) {
                        this.dependentMinDate = el.value;
                    }
                }
                if (this.field.maxDateField) {
                    const el = form.querySelector(`[dusk="${this.field.maxDateField}"]`);
                    if (el && el.value) {
                        this.dependentMaxDate = el.value;
                    }
                }
            });

            observer.observe(form, { childList: true, subtree: true, attributes: true });

            // Also listen for input events
            form.addEventListener("input", (e) => {
                const attr = e.target?.getAttribute("dusk");
                if (attr === this.field.minDateField) {
                    this.dependentMinDate = e.target.value;
                }
                if (attr === this.field.maxDateField) {
                    this.dependentMaxDate = e.target.value;
                }
            });
        },

        toISODate(date) {
            if (!date) return null;
            const d = date instanceof Date ? date : new Date(date);
            if (isNaN(d.getTime())) return null;
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, "0");
            const day = String(d.getDate()).padStart(2, "0");
            return `${year}-${month}-${day}`;
        },

        formatDate(date) {
            if (!date) return "";
            const d = date instanceof Date ? date : new Date(date);
            if (isNaN(d.getTime())) return "";
            const day = String(d.getDate()).padStart(2, "0");
            const month = String(d.getMonth() + 1).padStart(2, "0");
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },

        parseDisplayDate(dateStr) {
            if (!dateStr) return null;
            const parts = dateStr.split("/");
            if (parts.length === 3) {
                const d = new Date(parts[2], parts[1] - 1, parts[0]);
                if (!isNaN(d.getTime())) return d;
            }
            const d = new Date(dateStr + "T00:00:00");
            return isNaN(d.getTime()) ? null : d;
        },
    },
};
</script>
