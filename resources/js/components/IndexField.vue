<template>
    <span>
        <div v-if="visibleDates && visibleDates.length" class="flex flex-wrap gap-1">
            <span
                v-for="(i, idx) in visibleDates"
                :key="idx"
                class="inline-flex items-center bg-primary-100 text-primary-700 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-primary-800 dark:text-primary-200"
            >
                {{ i }}
            </span>
            <span
                v-if="remainingCount > 0"
                class="inline-flex items-center bg-gray-100 text-gray-600 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300"
            >
                +{{ remainingCount }} more
            </span>
        </div>
        <span v-else class="text-gray-400">&mdash;</span>
    </span>
</template>

<script>
export default {
    props: ["resourceName", "field"],

    computed: {
        fieldValue() {
            return this.field.displayedAs || this.field.value;
        },

        maxDisplay() {
            return this.field.maxDisplayOnIndex || 0;
        },

        visibleDates() {
            if (!this.fieldValue) return [];
            if (this.maxDisplay > 0) {
                return this.fieldValue.slice(0, this.maxDisplay);
            }
            return this.fieldValue;
        },

        remainingCount() {
            if (!this.fieldValue || !this.maxDisplay) return 0;
            return Math.max(0, this.fieldValue.length - this.maxDisplay);
        },
    },
};
</script>
