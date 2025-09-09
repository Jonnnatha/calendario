<template>
    <div class="p-4">
        <RoomNumberSelect v-model="selectedRoom" class="mb-4" />
        <CalendarView :events="events">
            <template #event="{ event }">
                <div class="event" :class="`event--${event.status}`">
                    {{ event.title }}
                </div>
            </template>
        </CalendarView>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { CalendarView } from 'vue-simple-calendar';
import RoomNumberSelect from '@/Components/RoomNumberSelect.vue';

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
        status: surgery.status,
    }))
);

const selectedRoom = ref(1);
</script>
