<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    surgeries: { type: Array, default: () => [] },
    rooms: { type: Array, default: () => [] },
    canCreate: { type: Boolean, default: false },
    canConfirm: { type: Boolean, default: false },
});

const form = useForm({
    patient_name: '',
    surgery_type: '',
    room: '',
    starts_at: '',
    duration_min: '',
});

const submit = () => {
    form.post(route('surgeries.store'));
};

const confirm = (id) => {
    router.post(route('surgeries.confirm', id), {}, {
        onSuccess: () => router.reload({ only: ['surgeries'] }),
    });
};

const rowClass = (s) => {
    if (s.status === 'conflito') {
        return 'bg-red-100 text-red-800 font-bold';
    }
    if (s.status === 'confirmado') {
        return 'bg-green-100 text-green-800';
    }
    return 'bg-blue-100 text-blue-800';
};
</script>

<template>
    <Head title="Calendar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cirurgias</h2>
        </template>

        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                v-if="canCreate"
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6"
            >
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel for="patient_name" value="Paciente" />
                        <TextInput
                            id="patient_name"
                            v-model="form.patient_name"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.patient_name" />
                    </div>

                    <div>
                        <InputLabel for="surgery_type" value="Tipo" />
                        <TextInput
                            id="surgery_type"
                            v-model="form.surgery_type"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.surgery_type" />
                    </div>

                    <div>
                        <InputLabel for="room" value="Sala" />
                        <select
                            id="room"
                            v-model="form.room"
                            class="mt-1 block w-full"
                        >
                            <option v-for="r in rooms" :key="r" :value="r">{{ r }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.room" />
                    </div>

                    <div>
                        <InputLabel for="starts_at" value="Início" />
                        <TextInput
                            id="starts_at"
                            type="datetime-local"
                            v-model="form.starts_at"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.starts_at" />
                    </div>

                    <div>
                        <InputLabel for="duration_min" value="Duração (min)" />
                        <TextInput
                            id="duration_min"
                            type="number"
                            v-model="form.duration_min"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.duration_min"
                        />
                    </div>

                    <PrimaryButton
                        class="mt-4"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Agendar
                    </PrimaryButton>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Paciente
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Tipo
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Sala
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Início
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Duração
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Fim
                            </th>
                            <th class="px-6 py-3" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr
                            v-for="s in surgeries"
                            :key="s.id"
                            :class="rowClass(s)"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ s.patient_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ s.surgery_type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ s.room }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ s.starts_at }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ s.duration_min }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ s.end_time }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button
                                    v-if="canConfirm && !s.confirmed_by"
                                    @click="confirm(s.id)"
                                    class="bg-green-500 text-white px-4 py-2 rounded"
                                >
                                    Confirmar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

