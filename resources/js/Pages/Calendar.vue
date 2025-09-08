<template>
    <div>
        <div v-if="$page.props.flash.message" class="mb-4 text-green-600">
            {{ $page.props.flash.message }}
        </div>
        <PrimaryButton @click="createSurgery">Create Surgery</PrimaryButton>
        <SecondaryButton class="ml-2" @click="confirmSurgery">Confirm Surgery</SecondaryButton>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

function createSurgery() {
    router.post(route('surgery.store'));
}

function confirmSurgery() {
    router.post(route('surgery.confirm'));
}
</script>
