<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    surgeries: {
        type: Array,
        default: () => [],
    },
});

const confirmSurgery = (id) => {
    router.post(route('surgeries.confirm', id));
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <template v-if="props.surgeries.length">
                        <table class="w-full text-left">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">ID</th>
                                    <th class="px-4 py-2">Paciente</th>
                                    <th class="px-4 py-2">Tipo</th>
                                    <th class="px-4 py-2">Status</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="surgery in props.surgeries" :key="surgery.id">
                                    <td class="border px-4 py-2">{{ surgery.id }}</td>
                                    <td class="border px-4 py-2">{{ surgery.patient_name }}</td>
                                    <td class="border px-4 py-2">{{ surgery.surgery_type }}</td>
                                    <td class="border px-4 py-2">{{ surgery.status }}</td>
                                    <td class="border px-4 py-2 text-right">
                                        <button @click="confirmSurgery(surgery.id)" class="bg-blue-500 text-white px-2 py-1 rounded">Confirmar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                    <template v-else>
                        <div class="text-gray-900">You're logged in!</div>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
