<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import RequestsByStatusChart from '@/components/RequestsByStatusChart.vue';
import RequestsPerCategoryChart from '@/components/RequestsPerCategoryChart.vue';
import RequestsPerMonthChart from '@/components/RequestsPerMonthChart.vue';
import TopMaterialsChart from '@/components/TopMaterialsChart.vue';
import { Card } from '@/components/ui/card';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { AlertTriangleIcon, ArchiveIcon, CheckCircleIcon, ClockIcon, InboxIcon, RefreshCwIcon } from 'lucide-vue-next';

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
        category_ids: number[];
    };
    categories: Array<{
        id: number;
        name: string;
    }>;
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-5">
                <Card class="relative flex flex-col items-center overflow-hidden border border-white/20 bg-primary py-6 text-white shadow-xl">
                    <div
                        class="pointer-events-none absolute inset-0"
                        style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(0, 0, 0, 0.08) 100%)"
                    ></div>
                    <ArchiveIcon class="z-10 mb-2 h-7 w-7" />
                    <div class="z-10 text-2xl font-bold">{{ props.stats.total }}</div>
                    <div class="z-10 mt-1 text-xs">Total Pedidos</div>
                </Card>
                <Card class="relative flex flex-col items-center overflow-hidden border border-yellow-100/40 bg-yellow-400 py-6 text-white shadow-xl">
                    <div
                        class="pointer-events-none absolute inset-0"
                        style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, rgba(0, 0, 0, 0.08) 100%)"
                    ></div>
                    <ClockIcon class="z-10 mb-2 h-7 w-7" />
                    <div class="z-10 text-2xl font-bold">{{ props.stats.pending }}</div>
                    <div class="z-10 mt-1 text-xs">Pendentes</div>
                </Card>
                <Card class="relative flex flex-col items-center overflow-hidden border border-green-100/30 bg-green-500 py-6 text-white shadow-xl">
                    <div
                        class="pointer-events-none absolute inset-0"
                        style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%)"
                    ></div>
                    <CheckCircleIcon class="z-10 mb-2 h-7 w-7" />
                    <div class="z-10 text-2xl font-bold">{{ props.stats.reserved }}</div>
                    <div class="z-10 mt-1 text-xs">Reservados</div>
                </Card>
                <Card class="relative flex flex-col items-center overflow-hidden border border-blue-100/30 bg-blue-500 py-6 text-white shadow-xl">
                    <div
                        class="pointer-events-none absolute inset-0"
                        style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%)"
                    ></div>
                    <RefreshCwIcon class="z-10 mb-2 h-7 w-7" />
                    <div class="z-10 text-2xl font-bold">{{ props.stats.ongoing }}</div>
                    <div class="z-10 mt-1 text-xs">Em Curso</div>
                </Card>
                <Card class="relative flex flex-col items-center overflow-hidden border border-red-100/30 bg-red-500 py-6 text-white shadow-xl">
                    <div
                        class="pointer-events-none absolute inset-0"
                        style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%)"
                    ></div>
                    <AlertTriangleIcon class="z-10 mb-2 h-7 w-7" />
                    <div class="z-10 text-2xl font-bold">{{ props.stats.overdue }}</div>
                    <div class="z-10 mt-1 text-xs">Atrasados</div>
                </Card>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <RequestsPerMonthChart :initialData="props.charts.requests_per_month" chartKey="requests_per_month" />
                <RequestsPerCategoryChart
                    :initialData="props.charts.requests_per_category"
                    chartKey="requests_per_category"
                    :categories="props.categories"
                />
            </div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <RequestsByStatusChart :initialData="props.charts.requests_by_status" chartKey="requests_by_status" />
                <TopMaterialsChart :initialData="props.charts.top_materials" chartKey="top_materials" />
            </div>

            <!-- Recent Activity -->
            <div>
                <Heading title="Atividade Recente" size="sm" class="mb-4" />
                <div v-if="props.recent.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <Card v-for="req in props.recent" :key="req.id" class="flex flex-col gap-2 p-4">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">{{ req.student }}</span>
                            <span class="ml-auto text-xs text-muted-foreground">#{{ req.id }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-muted-foreground">{{ req.requested_at }}</span>
                            <span
                                class="ml-auto rounded px-2 py-1 text-xs"
                                :class="{
                                    'bg-yellow-100 text-yellow-800': req.status === 'pendente',
                                    'bg-green-100 text-green-800': req.status === 'reservado',
                                    'bg-blue-100 text-blue-800': req.status === 'devolvido',
                                    'bg-red-100 text-red-800': req.status === 'cancelado',
                                }"
                            >
                                {{ req.status.charAt(0).toUpperCase() + req.status.slice(1) }}
                            </span>
                        </div>
                    </Card>
                </div>
                <div v-else class="flex flex-col items-center justify-center py-12">
                    <InboxIcon />
                    <div class="mb-2 text-lg font-medium">Nenhuma atividade recente</div>
                    <div class="text-muted-foreground">As atividades mais recentes aparecerão aqui.</div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
