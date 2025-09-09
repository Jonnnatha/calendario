<template>
    <div class="p-4">
        <form class="mb-4">
            <label for="room_number" class="mr-2">Sala</label>
            <select id="room_number" name="room_number" class="border rounded p-1">
                <option v-for="room in rooms" :key="room" :value="room">
                    Sala {{ room }}
                </option>
            </select>
        </form>

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
import 'vue-simple-calendar/dist/vue-simple-calendar.css';

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

const rooms = Array.from({ length: 9 }, (_, i) => i + 1);
</script>
