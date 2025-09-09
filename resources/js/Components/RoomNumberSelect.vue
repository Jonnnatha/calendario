<template>
    <select :value="modelValue" @change="$emit('update:modelValue', Number($event.target.value))">
        <option v-for="n in 9" :key="n" :value="n">{{ n }}</option>
    </select>
</template>

<script setup>
const props = defineProps({
    modelValue: {
        type: Number,
        default: 1,
    },
});
const emit = defineEmits(['update:modelValue']);
</script>
