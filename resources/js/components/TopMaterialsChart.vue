<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { router } from '@inertiajs/vue3';
import { CalendarIcon, FilterIcon, TrendingUpIcon } from 'lucide-vue-next';
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
const limit = ref(7);
const minRequests = ref(1);

// Limit options
const limitOptions = [
    { value: 5, label: 'Top 5' },
    { value: 7, label: 'Top 7' },
    { value: 10, label: 'Top 10' },
    { value: 15, label: 'Top 15' },
    { value: 20, label: 'Top 20' },
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
        type: 'bar',
        data: {
            labels: props.initialData.labels,
            datasets: [
                {
                    label: 'Requisições',
                    data: props.initialData.data,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16'],
                    borderColor: ['#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed', '#0891b2', '#65a30d'],
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            indexAxis: 'y',
            scales: {
                x: {
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
    if (limit.value !== 7) params.append('limit', limit.value.toString());
    if (minRequests.value !== 1) params.append('min_requests', minRequests.value.toString());

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
    limit.value = 7;
    minRequests.value = 1;
    applyFilters();
}

// Simple computed helpers
function hasActiveFilters() {
    return startDate.value || endDate.value || limit.value !== 7 || minRequests.value !== 1;
}

function getActiveFiltersCount() {
    let count = 0;
    if (startDate.value) count++;
    if (endDate.value) count++;
    if (limit.value !== 7) count++;
    if (minRequests.value !== 1) count++;
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
                <TrendingUpIcon class="h-5 w-5" />
                <h3 class="font-semibold">Top Materiais Requisitados</h3>
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

                        <!-- Limit Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Quantidade</label>
                            <div class="grid grid-cols-3 gap-2">
                                <Button
                                    v-for="option in limitOptions"
                                    :key="option.value"
                                    :variant="limit === option.value ? 'default' : 'outline'"
                                    size="sm"
                                    @click="limit = option.value"
                                >
                                    {{ option.label }}
                                </Button>
                            </div>
                        </div>

                        <!-- Min Requests Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Mínimo de Requisições</label>
                            <Input type="number" v-model.number="minRequests" min="1" max="100" placeholder="Mín. requisições" />
                            <p class="text-xs text-gray-600">
                                Mostrar apenas materiais com pelo menos {{ minRequests }}
                                {{ minRequests === 1 ? 'requisição' : 'requisições' }}
                            </p>
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

            <span v-if="limit !== 7" class="rounded bg-green-100 px-2 py-1 text-xs text-green-800"> Top {{ limit }} </span>

            <span v-if="minRequests !== 1" class="rounded bg-purple-100 px-2 py-1 text-xs text-purple-800"> Mín. {{ minRequests }} req. </span>
        </div>

        <!-- Chart -->
        <div class="relative h-64">
            <canvas ref="chartRef" class="h-full w-full"></canvas>
        </div>
    </Card>
</template>
