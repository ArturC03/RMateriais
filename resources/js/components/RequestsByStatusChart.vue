<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { router } from '@inertiajs/vue3';
import { CalendarIcon, FilterIcon, PieChartIcon } from 'lucide-vue-next';
import { nextTick, onMounted, ref } from 'vue';

const props = defineProps<{
    initialData: { labels: string[]; data: number[] };
    chartKey: string;
}>();

const chartRef = ref<HTMLCanvasElement>();
let chartInstance: any = null;
const isLoading = ref(false);
const showFilters = ref(false);

// Simple reactive filter state
const startDate = ref('');
const endDate = ref('');
const selectedStatuses = ref<string[]>([]);

// Status options
const statusOptions = [
    { value: 'pendente', label: 'Pendente', color: '#f59e0b' },
    { value: 'reservado', label: 'Reservado', color: '#10b981' },
    { value: 'devolvido', label: 'Devolvido', color: '#3b82f6' },
    { value: 'cancelado', label: 'Cancelado', color: '#ef4444' },
];

function createChart() {
    if (!chartRef.value) return;

    const Chart = (window as any).Chart;
    if (!Chart) return;

    // Destroy existing chart
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    const ctx = chartRef.value.getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: props.initialData.labels.map((label) => {
                const status = statusOptions.find((s) => s.value === label.toLowerCase());
                return status ? status.label : label;
            }),
            datasets: [
                {
                    label: 'Requisições',
                    data: props.initialData.data,
                    backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                },
            },
        },
    });
}

function applyFilters() {
    isLoading.value = true;
    showFilters.value = false;

    const params = new URLSearchParams();
    params.append('chart', props.chartKey);

    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);

    selectedStatuses.value.forEach((status) => {
        params.append('status_list[]', status);
    });

    // Use Inertia to reload with filters
    router.visit(`/dashboard?${params.toString()}`, {
        method: 'get',
        preserveState: false,
        preserveScroll: false,
        onFinish: () => {
            isLoading.value = false;
        },
    });
}

function clearFilters() {
    startDate.value = '';
    endDate.value = '';
    selectedStatuses.value = [];
    applyFilters();
}

function toggleStatus(statusValue: string) {
    const index = selectedStatuses.value.indexOf(statusValue);
    if (index > -1) {
        selectedStatuses.value.splice(index, 1);
    } else {
        selectedStatuses.value.push(statusValue);
    }
}

// Simple computed helpers
function hasActiveFilters() {
    return startDate.value || endDate.value || selectedStatuses.value.length > 0;
}

function getActiveFiltersCount() {
    let count = 0;
    if (startDate.value) count++;
    if (endDate.value) count++;
    if (selectedStatuses.value.length > 0) count += selectedStatuses.value.length;
    return count;
}

onMounted(async () => {
    await nextTick();
    createChart();
});
</script>

<template>
    <Card class="p-6">
        <!-- Chart Header -->
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <PieChartIcon class="h-5 w-5" />
                <h3 class="font-semibold">Requisições por Status</h3>
            </div>

            <!-- Filter Button -->
            <Popover v-model:open="showFilters">
                <PopoverTrigger as-child>
                    <Button variant="outline" size="sm">
                        <FilterIcon class="mr-2 h-4 w-4" />
                        Filtros
                        <span v-if="getActiveFiltersCount() > 0" class="ml-2 rounded-full bg-primary px-2 py-0.5 text-xs text-primary-foreground">
                            {{ getActiveFiltersCount() }}
                        </span>
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-80" align="end">
                    <div class="space-y-4">
                        <h4 class="font-medium">Filtros do Gráfico</h4>

                        <!-- Date Filters -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Período</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-xs text-gray-600">De</label>
                                    <Input type="date" v-model="startDate" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-600">Até</label>
                                    <Input type="date" v-model="endDate" />
                                </div>
                            </div>
                        </div>

                        <!-- Status Filters -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Status</label>
                            <div class="space-y-2">
                                <div v-for="status in statusOptions" :key="status.value" class="flex items-center space-x-2">
                                    <Checkbox
                                        :id="`status-${status.value}`"
                                        :checked="selectedStatuses.includes(status.value)"
                                        @click="toggleStatus(status.value)"
                                    />
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded-full" :style="{ backgroundColor: status.color }"></div>
                                        <label :for="`status-${status.value}`" class="cursor-pointer text-sm">
                                            {{ status.label }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 pt-2">
                            <Button @click="applyFilters" :disabled="isLoading" size="sm" class="flex-1">
                                {{ isLoading ? 'Aplicando...' : 'Aplicar' }}
                            </Button>
                            <Button v-if="hasActiveFilters()" @click="clearFilters" variant="outline" size="sm"> Limpar </Button>
                        </div>
                    </div>
                </PopoverContent>
            </Popover>
        </div>

        <!-- Active Filters Display -->
        <div v-if="hasActiveFilters()" class="mb-4 flex flex-wrap gap-2">
            <span class="text-xs text-gray-600">Filtros ativos:</span>

            <span v-if="startDate || endDate" class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-800">
                <CalendarIcon class="mr-1 inline h-3 w-3" />
                <span v-if="startDate && endDate">
                    {{ new Date(startDate).toLocaleDateString() }} - {{ new Date(endDate).toLocaleDateString() }}
                </span>
                <span v-else-if="startDate">Desde {{ new Date(startDate).toLocaleDateString() }}</span>
                <span v-else>Até {{ new Date(endDate).toLocaleDateString() }}</span>
            </span>

            <span v-for="statusValue in selectedStatuses" :key="statusValue" class="rounded bg-green-100 px-2 py-1 text-xs text-green-800">
                <div
                    class="mr-1 inline h-2 w-2 rounded-full"
                    :style="{ backgroundColor: statusOptions.find((s) => s.value === statusValue)?.color }"
                ></div>
                {{ statusOptions.find((s) => s.value === statusValue)?.label }}
            </span>
        </div>

        <!-- Chart -->
        <div class="relative h-64">
            <canvas ref="chartRef" class="h-full w-full"></canvas>
        </div>
    </Card>
</template>
