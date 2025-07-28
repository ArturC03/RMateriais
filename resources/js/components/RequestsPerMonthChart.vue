<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { router } from '@inertiajs/vue3';
import { CalendarIcon, FilterIcon, LineChartIcon } from 'lucide-vue-next';
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
const period = ref('month');

// Period options
const periodOptions = [
    { value: 'week', label: 'Semanal' },
    { value: 'month', label: 'Mensal' },
    { value: 'quarter', label: 'Trimestral' },
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
        type: 'line',
        data: {
            labels: props.initialData.labels,
            datasets: [
                {
                    label: 'Requisições',
                    data: props.initialData.data,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
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
    if (period.value !== 'month') params.append('period', period.value);

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
    period.value = 'month';
    applyFilters();
}

function setQuickRange(months: number) {
    const end = new Date();
    const start = new Date();
    start.setMonth(end.getMonth() - months);

    startDate.value = start.toISOString().split('T')[0];
    endDate.value = end.toISOString().split('T')[0];
}

// Simple computed helpers
function hasActiveFilters() {
    return startDate.value || endDate.value || period.value !== 'month';
}

function getActiveFiltersCount() {
    let count = 0;
    if (startDate.value) count++;
    if (endDate.value) count++;
    if (period.value !== 'month') count++;
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
                <LineChartIcon class="h-5 w-5" />
                <h3 class="font-semibold">Requisições ao Longo do Tempo</h3>
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

                        <!-- Quick Ranges -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Períodos Rápidos</label>
                            <div class="grid grid-cols-2 gap-2">
                                <Button variant="outline" size="sm" @click="setQuickRange(3)">3 meses</Button>
                                <Button variant="outline" size="sm" @click="setQuickRange(6)">6 meses</Button>
                                <Button variant="outline" size="sm" @click="setQuickRange(12)">1 ano</Button>
                                <Button variant="outline" size="sm" @click="setQuickRange(24)">2 anos</Button>
                            </div>
                        </div>

                        <!-- Period Type -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Agrupamento</label>
                            <div class="grid grid-cols-3 gap-2">
                                <Button
                                    v-for="option in periodOptions"
                                    :key="option.value"
                                    :variant="period === option.value ? 'default' : 'outline'"
                                    size="sm"
                                    @click="period = option.value"
                                >
                                    {{ option.label }}
                                </Button>
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

            <span v-if="period !== 'month'" class="rounded bg-green-100 px-2 py-1 text-xs text-green-800">
                {{ periodOptions.find((p) => p.value === period)?.label }}
            </span>
        </div>

        <!-- Chart -->
        <div class="relative h-64">
            <canvas ref="chartRef" class="h-full w-full"></canvas>
        </div>
    </Card>
</template>
