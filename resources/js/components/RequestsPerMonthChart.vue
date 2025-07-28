<script setup lang="ts">
import { ref, onMounted, watch, reactive } from 'vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover';
import { LineChartIcon, CalendarDays } from 'lucide-vue-next';

const props = defineProps<{ initialData: { labels: string[]; data: number[] }; chartKey: string }>();

const chartData = ref({ labels: [...props.initialData.labels], data: [...props.initialData.data] });
const filter = reactive({ start: '', end: '' });
const loading = ref(false);
const chartRef = ref();
let chartInstance: any = null;

function renderChart() {
  const Chart = (window as any).Chart;
  if (!Chart || !chartRef.value) return;
  if (chartInstance) chartInstance.destroy();
  const styles = getComputedStyle(document.documentElement);
  const chart1Color = styles.getPropertyValue('--color-chart-1').trim() || styles.getPropertyValue('--color-primary').trim();
  const borderColor = styles.getPropertyValue('--color-border').trim() || '#e5e7eb';
  chartInstance = new Chart(chartRef.value, {
    type: 'line',
    data: {
      labels: chartData.value.labels,
      datasets: [{
        label: 'Requisições',
        data: chartData.value.data,
        borderColor: chart1Color,
        backgroundColor: chart1Color + '22',
        tension: 0.4,
      }],
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: borderColor } },
        y: { grid: { color: borderColor } }
      }
    },
  });
}

async function applyFilter() {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    params.append('chart', props.chartKey);
    if (filter.start) params.append('start', filter.start);
    if (filter.end) params.append('end', filter.end);
    const res = await fetch(`/dashboard?${params.toString()}`, { headers: { 'X-Inertia': 'true', 'Accept': 'application/json' } });
    if (res.ok) {
      const data = await res.json();
      if (data?.props?.charts?.[props.chartKey]) {
        chartData.value.labels = data.props.charts[props.chartKey].labels;
        chartData.value.data = data.props.charts[props.chartKey].data;
        renderChart();
      }
    }
  } finally {
    loading.value = false;
  }
}

onMounted(renderChart);
watch(
  () => [chartData.value.labels, chartData.value.data],
  () => renderChart(),
  { deep: true }
);
</script>

<template>
  <Card class="p-6 flex flex-col">
    <div class="flex items-center gap-2 mb-4">
      <LineChartIcon/>
      <span class="font-semibold">Requisições por mês</span>
      <Popover>
        <PopoverTrigger as-child>
          <Button variant="ghost" size="icon" class="ml-2" aria-label="Filtrar por data">
            <CalendarDays class="w-5 h-5 opacity-70" />
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-64">
          <div class="flex flex-col gap-2">
            <label class="text-xs font-medium">Data inicial</label>
            <Input type="date" v-model="filter.start" />
            <label class="text-xs font-medium">Data final</label>
            <Input type="date" v-model="filter.end" />
            <Button @click="applyFilter" :disabled="loading" class="mt-2" block>
              <span v-if="loading">A aplicar...</span>
              <span v-else>Aplicar</span>
            </Button>
          </div>
        </PopoverContent>
      </Popover>
    </div>
    <canvas ref="chartRef" height="180"></canvas>
  </Card>
</template>
