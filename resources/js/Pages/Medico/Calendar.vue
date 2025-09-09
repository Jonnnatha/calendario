<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RoomNumberSelect from '@/Components/RoomNumberSelect.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    surgeries: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
});

const page = usePage();
const user = page.props.auth.user;
const isMedico = computed(() => user.roles.includes('medico'));
const isEnfermeiro = computed(() => user.roles.includes('enfermeiro'));

const form = useForm({
    doctor_id: user.id,
    patient_name: '',
    surgery_type: '',
    room: 1,
    starts_at: '',
    duration_min: '',
    end_time: '',
});

const computeEnd = (start, duration) => {
    const startDate = new Date(start);
    return new Date(startDate.getTime() + Number(duration) * 60000).toISOString();
};

const submit = () => {
    form.end_time = computeEnd(form.starts_at, form.duration_min);
    form.post(route('surgeries.store'));
};

const confirm = (id) => {
    router.post(route('surgeries.confirm', id));
};

const surgeries = computed(() => props.surgeries.data || props.surgeries);
const links = computed(() => props.surgeries.links || []);

const endTime = (s) => s.end_time || computeEnd(s.starts_at, s.duration_min);

const rowClass = (s) => {
    if (s.is_conflict || s.status === 'conflict') {
        return 'bg-red-100 text-red-800 font-bold';
    }
    if (s.status === 'confirmed') {
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
                v-if="isMedico"
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
                        <RoomNumberSelect
                            id="room"
                            v-model="form.room"
                            class="mt-1"
                        />
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
                            v-for="surgery in surgeries"
                            :key="surgery.id"
                            :class="rowClass(surgery)"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ surgery.patient_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ surgery.surgery_type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ surgery.room }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ surgery.starts_at }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ surgery.duration_min }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ endTime(surgery) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button
                                    v-if="isEnfermeiro && !surgery.confirmed_by"
                                    @click="confirm(surgery.id)"
                                    class="bg-green-500 text-white px-4 py-2 rounded"
                                >
                                    Confirmar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="links.length" class="p-4">
                    <nav class="flex space-x-2">
                        <template v-for="link in links" :key="link.url || link.label">
                            <a
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1 rounded border"
                                :class="{ 'bg-gray-200': link.active }"
                            />
                            <span
                                v-else
                                v-html="link.label"
                                class="px-3 py-1 text-gray-500"
                            />
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

