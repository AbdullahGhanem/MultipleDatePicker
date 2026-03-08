<template>
    <PanelItem :index="index" :field="field">
        <template #value>
            <!-- Calendar heatmap view -->
            <div v-if="field.calendarView && field.value && field.value.length" class="mdp-calendar-view">
                <div class="mdp-calendar-grid">
                    <div class="mdp-calendar-header">
                        <button type="button" class="mdp-cal-nav" @click="prevMonth">&lsaquo;</button>
                        <span class="mdp-cal-title">{{ monthYearLabel }}</span>
                        <button type="button" class="mdp-cal-nav" @click="nextMonth">&rsaquo;</button>
                    </div>
                    <div class="mdp-cal-weekdays">
                        <span v-for="d in weekdays" :key="d" class="mdp-cal-weekday">{{ d }}</span>
                    </div>
                    <div class="mdp-cal-days">
                        <span
                            v-for="(day, idx) in calendarDays"
                            :key="idx"
                            class="mdp-cal-day"
                            :class="{
                                'mdp-cal-day-empty': !day,
                                'mdp-cal-day-selected': day && isDateSelected(day),
                                'mdp-cal-day-today': day && isToday(day),
                            }"
                        >
                            {{ day || '' }}
                        </span>
                    </div>
                </div>
                <div class="mdp-calendar-summary">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ field.value.length }} date(s) selected
                    </span>
                </div>
            </div>

            <!-- Default badge view -->
            <div v-else-if="field.value && field.value.length" class="flex flex-wrap gap-1">
                <span
                    v-for="(i, idx) in field.value"
                    :key="idx"
                    class="inline-flex items-center bg-primary-100 text-primary-700 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-primary-800 dark:text-primary-200"
                >
                    {{ i }}
                </span>
            </div>
            <span v-else class="text-gray-400">&mdash;</span>
        </template>
    </PanelItem>
</template>

<script>
export default {
    props: ["index", "resource", "resourceName", "resourceId", "field"],

    data() {
        return {
            currentMonth: new Date().getMonth(),
            currentYear: new Date().getFullYear(),
        };
    },

    computed: {
        weekdays() {
            return ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
        },

        monthYearLabel() {
            const months = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December",
            ];
            return `${months[this.currentMonth]} ${this.currentYear}`;
        },

        calendarDays() {
            const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
            const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
            const days = [];

            for (let i = 0; i < firstDay; i++) {
                days.push(null);
            }
            for (let i = 1; i <= daysInMonth; i++) {
                days.push(i);
            }
            return days;
        },

        parsedSelectedDates() {
            if (!this.field.value) return [];
            return this.field.value.map((dateStr) => {
                const parts = dateStr.split("/");
                if (parts.length === 3) {
                    return `${parts[2]}-${parts[1]}-${parts[0]}`;
                }
                return dateStr;
            });
        },
    },

    mounted() {
        if (this.field.calendarView && this.field.value && this.field.value.length) {
            const firstDate = this.parsedSelectedDates[0];
            if (firstDate) {
                const d = new Date(firstDate + "T00:00:00");
                if (!isNaN(d.getTime())) {
                    this.currentMonth = d.getMonth();
                    this.currentYear = d.getFullYear();
                }
            }
        }
    },

    methods: {
        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
        },

        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
        },

        isDateSelected(day) {
            const month = String(this.currentMonth + 1).padStart(2, "0");
            const dayStr = String(day).padStart(2, "0");
            const isoDate = `${this.currentYear}-${month}-${dayStr}`;
            return this.parsedSelectedDates.includes(isoDate);
        },

        isToday(day) {
            const now = new Date();
            return (
                day === now.getDate() &&
                this.currentMonth === now.getMonth() &&
                this.currentYear === now.getFullYear()
            );
        },
    },
};
</script>
