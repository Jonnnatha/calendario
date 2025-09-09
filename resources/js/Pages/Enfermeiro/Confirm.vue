<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const surgeryId = ref('');

const confirmSurgery = () => {
    if (surgeryId.value) {
        router.post(route('surgeries.confirm', surgeryId.value));
    }
};
</script>

<template>
    <Head title="Confirm Surgery" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Confirm Surgery</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center">
                    <input v-model="surgeryId" type="text" placeholder="Surgery ID" class="border rounded px-2 py-1" />
                    <button @click="confirmSurgery" class="ml-2 bg-blue-500 text-white px-4 py-2 rounded">Confirm</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
