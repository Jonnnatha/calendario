<template>
    <div class="p-4">
        <CalendarView :events="events">
            <template #event="{ event }">
                <div class="p-1">
                    {{ event.title }}
                </div>
            </template>
        </CalendarView>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { CalendarView } from 'vue-simple-calendar';

const props = defineProps({
    surgeries: {
        type: Array,
        default: () => [],
    },
});

const events = computed(() =>
    props.surgeries.map((surgery) => ({
        id: surgery.id,
        startDate: new Date(surgery.start_time),
        endDate: new Date(surgery.end_time),
        title: `Sala ${surgery.room_number}`,
    }))
);
</script>
