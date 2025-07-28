<script setup lang="ts">
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { ref } from 'vue';

const props = defineProps<{
  requests: Array<{
    id: number;
    user: { name: string };
    status: string;
    requested_at: string;
    request_items?: Array<any>;
  }>;
}>();

const showViewSheet = ref<number|null>(null);

const getStatusColor = (status: string) => {
  const colors = {
    pendente: 'bg-yellow-100 text-yellow-800',
    reservado: 'bg-green-100 text-green-800',
    devolvido: 'bg-blue-100 text-blue-800',
    cancelado: 'bg-red-100 text-red-800',
  };
  return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};
function openViewSheet(id: number) {
  showViewSheet.value = id;
}
function closeViewSheet() {
  showViewSheet.value = null;
}
function isViewSheetOpen(id: number) {
  return showViewSheet.value === id;
}
</script>

<template>
  <Head title="Requisições" />
  <AppLayout>
    <div class="container mx-auto px-4 py-6 sm:py-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <Heading title="Gestão de Requisições" />
      </div>
      <Card class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b">
              <th class="py-2 px-2 text-left">ID</th>
              <th class="py-2 px-2 text-left">Aluno</th>
              <th class="py-2 px-2 text-left">Status</th>
              <th class="py-2 px-2 text-left">Data</th>
              <th class="py-2 px-2 text-left">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="req in props.requests" :key="req.id" class="border-b hover:bg-muted/30">
              <td class="py-2 px-2 font-mono">#{{ req.id }}</td>
              <td class="py-2 px-2">{{ req.user.name }}</td>
              <td class="py-2 px-2">
                <Badge :variant="req.status === 'pendente' ? 'secondary' : req.status === 'reservado' ? 'default' : 'outline'" :class="getStatusColor(req.status)">
                  {{ req.status.charAt(0).toUpperCase() + req.status.slice(1) }}
                </Badge>
              </td>
              <td class="py-2 px-2">{{ req.requested_at }}</td>
              <td class="py-2 px-2 flex gap-2">
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger as-child>
                      <Button v-if="req.status === 'pendente'" variant="outline" size="icon">
                        <span class="sr-only">Aprovar</span>
                        <i class="lucide lucide-check w-4 h-4" />
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Aprovar</TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger as-child>
                      <Button v-if="req.status === 'pendente'" variant="destructive" size="icon">
                        <span class="sr-only">Rejeitar</span>
                        <i class="lucide lucide-x w-4 h-4" />
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Rejeitar</TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger as-child>
                      <Button variant="outline" size="icon" @click="openViewSheet(req.id)">
                        <span class="sr-only">Ver</span>
                        <i class="lucide lucide-eye w-4 h-4" />
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Ver Detalhes</TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <!-- View Sheet -->
                <Sheet :open="isViewSheetOpen(req.id)" @update:open="val => { if (!val) closeViewSheet(); }">
                  <SheetContent side="right" class="w-full max-w-md">
                    <SheetHeader>
                      <SheetTitle>Detalhes da Requisição</SheetTitle>
                    </SheetHeader>
                    <div class="mt-4 text-muted-foreground text-sm">[Detalhes da requisição #{{ req.id }}]</div>
                  </SheetContent>
                </Sheet>
              </td>
            </tr>
            <tr v-if="props.requests.length === 0">
              <td colspan="5" class="text-center text-muted-foreground py-4">Sem requisições registadas.</td>
            </tr>
          </tbody>
        </table>
      </Card>
    </div>
  </AppLayout>
</template>
