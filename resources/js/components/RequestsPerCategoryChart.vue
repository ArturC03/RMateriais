<script setup lang="ts">
import { ref, onMounted, watch, computed, nextTick, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover';
import { Checkbox } from '@/components/ui/checkbox';
import { BarChartIcon, Layers } from 'lucide-vue-next';

const props = defineProps<{ initialData: { labels: string[]; data: number[] }; chartKey: string }>();
const chartData = ref(props.initialData);
const chartRef = ref();
const chartInstance = ref<any>(null);
const categories = ref<{ id: number; name: string }[]>([]);
const selectedCategories = ref<number[]>([]);
const popoverOpen = ref(false);
const tempSelectedCategories = ref<number[]>([]);

// Helper: map category name to ID and vice versa
const categoryNameToId = () => {
  const map: Record<string, number> = {};
  categories.value.forEach(cat => { map[cat.name] = cat.id; });
  return map;
};
const categoryIdToName = () => {
  const map: Record<number, string> = {};
  categories.value.forEach(cat => { map[cat.id] = cat.name; });
  return map;
};

function destroyChart() {
  if (chartInstance.value) {
    try {
      chartInstance.value.destroy();
    } catch (error) {
      console.error('Error destroying chart:', error);
    }
    chartInstance.value = null;
  }
}

function createChart() {
  const Chart = (window as any).Chart;
  if (!Chart) {
    console.error('Chart.js not available');
    return;
  }
  
  if (!chartRef.value) {
    console.error('Canvas element not found');
    return;
  }

  try {
    // Always destroy existing chart first
    destroyChart();
    
    const canvas = chartRef.value;
    const ctx = canvas.getContext('2d');
    
    if (!ctx) {
      console.error('Could not get canvas context');
      return;
    }

    const styles = getComputedStyle(document.documentElement);
    const chart1Color = styles.getPropertyValue('--color-chart-1').trim() || styles.getPropertyValue('--color-primary').trim();
    const chart2Color = styles.getPropertyValue('--color-chart-2').trim() || '#a78bfa';
    const chart3Color = styles.getPropertyValue('--color-chart-3').trim() || '#fbbf24';
    const chart4Color = styles.getPropertyValue('--color-chart-4').trim() || '#f87171';
    const borderColor = styles.getPropertyValue('--color-border').trim() || '#e5e7eb';
    
    chartInstance.value = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: chartData.value.labels,
        datasets: [{
          label: 'Materiais',
          data: chartData.value.data,
          backgroundColor: [chart1Color, chart2Color, chart3Color, chart4Color],
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: borderColor } },
          y: { grid: { color: borderColor } }
        }
      },
    });
    
    console.log('Chart created successfully');
  } catch (error) {
    console.error('Error creating chart:', error);
  }
}

function openPopover() {
  tempSelectedCategories.value = [...selectedCategories.value];
  popoverOpen.value = true;
}
function closePopover() {
  popoverOpen.value = false;
}
function applyCategoryFilter() {
  selectedCategories.value = [...tempSelectedCategories.value];
  popoverOpen.value = false;
  console.log('Applying filter with categories:', selectedCategories.value);
  // Send selected categories to backend with Inertia POST
  router.post(
    '/dashboard',
    { category_ids: selectedCategories.value },
    { 
      preserveScroll: true, 
      preserveState: true,
      onSuccess: () => {
        console.log('Filter applied successfully');
      },
      onError: (errors) => {
        console.error('Filter error:', errors);
      }
    }
  );
}
function cancelCategoryFilter() {
  closePopover();
}

function isCategoryChecked(id: number) {
  return tempSelectedCategories.value.includes(id);
}
function setCategoryChecked(id: number, checked: boolean) {
  if (checked) {
    if (!tempSelectedCategories.value.includes(id)) tempSelectedCategories.value.push(id);
  } else {
    const idx = tempSelectedCategories.value.indexOf(id);
    if (idx !== -1) tempSelectedCategories.value.splice(idx, 1);
  }
  console.log('tempSelectedCategories now:', tempSelectedCategories.value);
}
const categoryCheckedProxy = computed(() => {
  const proxy: Record<number, boolean> = {};
  categories.value.forEach(cat => {
    Object.defineProperty(proxy, cat.id, {
      get: () => isCategoryChecked(cat.id),
      set: (val: boolean) => setCategoryChecked(cat.id, val),
      enumerable: true,
      configurable: true,
    });
  });
  return proxy;
});

onMounted(async () => {
  console.log('Component mounted, initial data:', props.initialData);
  
  // Wait for next tick to ensure DOM is ready
  await nextTick();
  
  // Fetch categories for filter
  try {
    const res = await fetch('/dashboard/categories');
    if (res.ok) {
      categories.value = await res.json();
      console.log('Categories loaded:', categories.value);
      // On first load, select all categories that are present in the chart
      const nameToId: Record<string, number> = {};
      categories.value.forEach(cat => { nameToId[cat.name] = cat.id; });
      selectedCategories.value = props.initialData.labels.map(label => nameToId[label]).filter(Boolean);
      console.log('Selected categories on load:', selectedCategories.value);
    }
  } catch (e) {
    console.error('Error loading categories:', e);
  }
  
  // Create chart after everything is set up
  setTimeout(() => {
    console.log('Canvas element:', chartRef.value);
    if (chartRef.value) {
      console.log('Canvas dimensions:', chartRef.value.width, 'x', chartRef.value.height);
      createChart();
    } else {
      console.error('Canvas not found after timeout');
    }
  }, 500);
});

// Cleanup on unmount
onUnmounted(() => {
  destroyChart();
});

// Watch for data changes and recreate chart
watch(() => props.initialData, (newData) => {
  console.log('Chart data updated:', newData);
  chartData.value = { ...newData }; // Create a new object to ensure reactivity
  
  // Recreate chart with new data
  createChart();
}, { deep: true });
</script>

<template>
  <Card class="p-6 flex flex-col">
    <div class="flex items-center gap-2 mb-4">
      <BarChartIcon/>
      <span class="font-semibold">Materiais por categoria</span>
      <Popover v-model:open="popoverOpen">
        <PopoverTrigger as-child>
          <Button variant="ghost" size="icon" class="ml-2" aria-label="Filtrar por categoria" @click="openPopover">
            <Layers class="w-5 h-5 opacity-70" />
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-64">
          <div class="flex flex-col gap-2">
            <label class="text-xs font-medium mb-1">Categorias</label>
            <div class="max-h-40 overflow-y-auto mb-2">
              <div v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 py-1">
                <Checkbox
                  v-model:checked="categoryCheckedProxy[cat.id]"
                  id="cat-{{cat.id}}"
                />
                <label :for="'cat-' + cat.id" class="text-sm cursor-pointer">{{ cat.name }}</label>
              </div>
            </div>
            <div class="flex gap-2 mt-2">
              <Button size="sm" variant="default" class="flex-1" @click="applyCategoryFilter">Aplicar</Button>
              <Button size="sm" variant="outline" class="flex-1" @click="cancelCategoryFilter">Cancelar</Button>
            </div>
          </div>
        </PopoverContent>
      </Popover>
      <span class="ml-2 text-xs text-muted-foreground">
        {{ selectedCategories.length === 0 ? 'Todas' : selectedCategories.length + ' selecionadas' }}
      </span>
    </div>
    <div class="relative w-full h-48">
      <canvas ref="chartRef" class="w-full h-full" width="400" height="200"></canvas>
    </div>
  </Card>
</template>
