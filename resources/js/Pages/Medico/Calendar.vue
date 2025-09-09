<template>
    <div class="p-4">
        <RoomNumberSelect v-model="selectedRoom" class="mb-4" />
        <FullCalendar :options="options" />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import '@fullcalendar/daygrid/index.css';
import '@fullcalendar/timegrid/index.css';
import RoomNumberSelect from '@/Components/RoomNumberSelect.vue';

const props = defineProps({
    surgeries: {
        type: Array,
        default: () => [],
    },
});
const selectedRoom = ref(1);

const events = computed(() =>
    props.surgeries
        .filter((surgery) => surgery.room_number === selectedRoom.value)
        .map((surgery) => ({
            id: surgery.id,
            start: surgery.start_time,
            end: surgery.end_time,
            title: `Sala ${surgery.room_number}`,
            extendedProps: { status: surgery.status },
        }))
);

const options = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    events: events.value,
    eventClassNames({ event }) {
        const status = event.extendedProps.status;
        return ['event', status === 'conflict' ? 'event--conflict' : `event--${status}`];
    },
}));
</script>
