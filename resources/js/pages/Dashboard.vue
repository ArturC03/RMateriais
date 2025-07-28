<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import Heading from '@/components/Heading.vue';
import { Card } from '@/components/ui/card';
import { onMounted, ref, watch } from 'vue';
import { ArchiveIcon, ClockIcon, CheckCircleIcon,RefreshCwIcon,AlertTriangleIcon,InboxIcon} from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import RequestsPerMonthChart from '@/components/RequestsPerMonthChart.vue';
import RequestsPerCategoryChart from '@/components/RequestsPerCategoryChart.vue';
import RequestsByStatusChart from '@/components/RequestsByStatusChart.vue';
import TopMaterialsChart from '@/components/TopMaterialsChart.vue';

const props = defineProps<{
  stats: {
    total: number;
    pending: number;
    reserved: number;
    overdue: number;
    ongoing: number;
  };
  recent: Array<{
    id: number;
    student: string;
    status: string;
    requested_at: string;
  }>;
  charts: {
    requests_per_month: {
      labels: string[];
      data: number[];
    };
    requests_per_category: {
      labels: string[];
      data: number[];
    };
    requests_by_status: {
      labels: string[];
      data: number[];
    };
    top_materials: {
      labels: string[];
      data: number[];
    };
  };
  filters: {
    start_date: string;
    end_date: string;
  };
}>();

const requestsPerMonthChart = ref();
const requestsPerCategoryChart = ref();
const requestsByStatusChart = ref();
const topMaterialsChart = ref();

const form = useForm({
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || '',
});

function reloadWithFilters() {
  form.get(route('dashboard'), { preserveState: true });
}

function renderCharts() {
  const Chart = (window as any).Chart;
  if (Chart && requestsPerMonthChart.value && requestsPerCategoryChart.value && requestsByStatusChart.value && topMaterialsChart.value && props.charts) {
    const styles = getComputedStyle(document.documentElement);
    const chart1Color = styles.getPropertyValue('--color-chart-1').trim() || styles.getPropertyValue('--color-primary').trim();
    const chart2Color = styles.getPropertyValue('--color-chart-2').trim() || '#a78bfa';

    const chart3Color = styles.getPropertyValue('--color-chart-3').trim() || '#fbbf24';
    const chart4Color = styles.getPropertyValue('--color-chart-4').trim() || '#f87171';
    const borderColor = styles.getPropertyValue('--color-border').trim() || '#e5e7eb';

    // Destroy previous charts if they exist
    [requestsPerMonthChart, requestsPerCategoryChart, requestsByStatusChart, topMaterialsChart].forEach(refEl => {
      if (refEl.value && refEl.value._chartInstance) {
        refEl.value._chartInstance.destroy();
      }
    });

    // Requests per month (line)
    requestsPerMonthChart.value._chartInstance = new Chart(requestsPerMonthChart.value, {
      type: 'line',
      data: {
        labels: props.charts.requests_per_month.labels,
        datasets: [{
          label: 'Requisições',
          data: props.charts.requests_per_month.data,
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

    // Requests per category (bar)
    requestsPerCategoryChart.value._chartInstance = new Chart(requestsPerCategoryChart.value, {
      type: 'bar',
      data: {
        labels: props.charts.requests_per_category.labels,
        datasets: [{
          label: 'Materiais',
          data: props.charts.requests_per_category.data,
          backgroundColor: [chart1Color, chart2Color, chart3Color, chart4Color],
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

    // Requests by status (pie/donut)
    requestsByStatusChart.value._chartInstance = new Chart(requestsByStatusChart.value, {
      type: 'doughnut',
      data: {
        labels: props.charts.requests_by_status.labels,
        datasets: [{
          label: 'Status',
          data: props.charts.requests_by_status.data,
          backgroundColor: [chart1Color, chart2Color, chart3Color, chart4Color],
        }],
      },
      options: {
        responsive: true,
        plugins: { legend: { display: true, position: 'bottom' } },
      },
    });

    // Top requested materials (bar)
    topMaterialsChart.value._chartInstance = new Chart(topMaterialsChart.value, {
      type: 'bar',
      data: {
        labels: props.charts.top_materials.labels,
        datasets: [{
          label: 'Top Materiais',
          data: props.charts.top_materials.data,
          backgroundColor: [chart1Color, chart2Color, chart3Color, chart4Color, chart1Color, chart2Color, chart3Color],
        }],
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        indexAxis: 'y',
        scales: {
          x: { grid: { color: borderColor } },
          y: { grid: { color: borderColor } }
        }
      },
    });
  }
}

onMounted(renderCharts);
watch(() => props.charts, renderCharts);
</script>

<template>
  <AppSidebarLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
    <div class="px-4 py-8 max-w-7xl mx-auto flex flex-col gap-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-6">
        <Card class="relative flex flex-col items-center py-6 bg-primary text-white shadow-xl overflow-hidden border border-white/20">
          <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(135deg,rgba(255,255,255,0.08) 0%,rgba(0,0,0,0.08) 100%);"></div>
          <ArchiveIcon class="w-7 h-7 mb-2 z-10" />
          <div class="text-2xl font-bold z-10">{{ props.stats.total }}</div>
          <div class="text-xs mt-1 z-10">Total Pedidos</div>
        </Card>
        <Card class="relative flex flex-col items-center py-6 bg-yellow-400 text-white shadow-xl overflow-hidden border border-yellow-100/40">
          <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(135deg,rgba(255,255,255,0.12) 0%,rgba(0,0,0,0.08) 100%);"></div>
          <ClockIcon class="w-7 h-7 mb-2 z-10" />
          <div class="text-2xl font-bold z-10">{{ props.stats.pending }}</div>
          <div class="text-xs mt-1 z-10">Pendentes</div>
        </Card>
        <Card class="relative flex flex-col items-center py-6 bg-green-500 text-white shadow-xl overflow-hidden border border-green-100/30">
          <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(135deg,rgba(255,255,255,0.10) 0%,rgba(0,0,0,0.10) 100%);"></div>
          <CheckCircleIcon class="w-7 h-7 mb-2 z-10" />
          <div class="text-2xl font-bold z-10">{{ props.stats.reserved }}</div>
          <div class="text-xs mt-1 z-10">Reservados</div>
        </Card>
        <Card class="relative flex flex-col items-center py-6 bg-blue-500 text-white shadow-xl overflow-hidden border border-blue-100/30">
          <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(135deg,rgba(255,255,255,0.10) 0%,rgba(0,0,0,0.10) 100%);"></div>
          <RefreshCwIcon class="w-7 h-7 mb-2 z-10" />
          <div class="text-2xl font-bold z-10">{{ props.stats.ongoing }}</div>
          <div class="text-xs mt-1 z-10">Em Curso</div>
        </Card>
        <Card class="relative flex flex-col items-center py-6 bg-red-500 text-white shadow-xl overflow-hidden border border-red-100/30">
          <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(135deg,rgba(255,255,255,0.10) 0%,rgba(0,0,0,0.10) 100%);"></div>
          <AlertTriangleIcon class="w-7 h-7 mb-2 z-10" />
          <div class="text-2xl font-bold z-10">{{ props.stats.overdue }}</div>
          <div class="text-xs mt-1 z-10">Atrasados</div>
        </Card>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <RequestsPerMonthChart
          :initialData="props.charts.requests_per_month"
          chartKey="requests_per_month"
          ref="requestsPerMonthChart"
        />
        <RequestsPerCategoryChart
          :initialData="props.charts.requests_per_category"
          chartKey="requests_per_category"
          ref="requestsPerCategoryChart"
        />
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <RequestsByStatusChart
          :initialData="props.charts.requests_by_status"
          chartKey="requests_by_status"
          ref="requestsByStatusChart"
        />
        <TopMaterialsChart
          :initialData="props.charts.top_materials"
          chartKey="top_materials"
          ref="topMaterialsChart"
        />
      </div>

      <!-- Recent Activity -->
      <div>
        <Heading title="Atividade Recente" size="sm" class="mb-4" />
        <div v-if="props.recent.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Card v-for="req in props.recent" :key="req.id" class="flex flex-col gap-2 p-4">
            <div class="flex items-center gap-2">
              <span class="font-semibold">{{ req.student }}</span>
              <span class="ml-auto text-xs text-muted-foreground">#{{ req.id }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs text-muted-foreground">{{ req.requested_at }}</span>
              <span class="ml-auto px-2 py-1 rounded text-xs" :class="{
                'bg-yellow-100 text-yellow-800': req.status === 'pendente',
                'bg-green-100 text-green-800': req.status === 'reservado',
                'bg-blue-100 text-blue-800': req.status === 'devolvido',
                'bg-red-100 text-red-800': req.status === 'cancelado',
              }">
                {{ req.status.charAt(0).toUpperCase() + req.status.slice(1) }}
              </span>
            </div>
          </Card>
        </div>
        <div v-else class="flex flex-col items-center justify-center py-12">
            <InboxIcon/>
          <div class="text-lg font-medium mb-2">Nenhuma atividade recente</div>
          <div class="text-muted-foreground">As atividades mais recentes aparecerão aqui.</div>
        </div>
      </div>
    </div>
  </AppSidebarLayout>
</template>
