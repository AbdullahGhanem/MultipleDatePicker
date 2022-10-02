<template>
    <PanelItem :index="index" :field="field">
        <template #value>
            <div
                v-for="(i, index) in field.value"
                :key="index"
                class="inline-grid"
            >
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
        </template>
    </PanelItem>
</template>

<script>
export default {
    props: ["index", "resource", "resourceName", "resourceId", "field"],
};
</script>
