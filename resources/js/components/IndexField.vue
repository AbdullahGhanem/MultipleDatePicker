<template>
    <span>
        <div v-for="(i, index) in fieldValue" :key="index" class="inline-grid">
            <span
                class="
                    bg-blue-100
                    text-blue-800 text-xs
                    font-semibold
                    mr-2
                    px-2.5
                    py-0.5
                    rounded
                    dark:bg-blue-200 dark:text-blue-800
                "
            >
                {{ i }}</span
            >
        </div>
    </span>
</template>

<script>
export default {
    props: ["resourceName", "field"],

    computed: {
        fieldValue() {
            return this.field.displayedAs || this.field.value;
        },
    },
};
</script>
