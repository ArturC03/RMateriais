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
import { PencilIcon, TrashIcon } from 'lucide-vue-next';

const props = defineProps<{
  materials: Array<{
    id: number;
    name: string;
    description: string;
    quantity: number;
    max_days_per_request: number;
    category?: { name: string };
  }>;
}>();

const showAddSheet = ref(false);
const showEditSheet = ref<number|null>(null);

function openEditSheet(id: number) {
  showEditSheet.value = id;
}
function closeEditSheet() {
  showEditSheet.value = null;
}
function isEditSheetOpen(id: number) {
  return showEditSheet.value === id;
}
</script>

<template>
  <Head title="Materiais" />
  <AppLayout>
    <div class="container mx-auto px-4 py-6 sm:py-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <Heading title="Gestão de Materiais" />
        <Sheet v-model:open="showAddSheet">
          <SheetTrigger as-child>
            <Button class="w-full sm:w-auto">
              Adicionar Material
            </Button>
          </SheetTrigger>
          <SheetContent side="right" class="w-full max-w-md">
            <SheetHeader>
              <SheetTitle>Adicionar Material</SheetTitle>
            </SheetHeader>
            <div class="mt-4 text-muted-foreground text-sm">[Formulário de adicionar material]</div>
          </SheetContent>
        </Sheet>
      </div>
      <Card class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b">
              <th class="py-2 px-2 text-left">Nome</th>
              <th class="py-2 px-2 text-left">Categoria</th>
              <th class="py-2 px-2 text-left">Stock</th>
              <th class="py-2 px-2 text-left">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="mat in props.materials" :key="mat.id" class="border-b hover:bg-muted/30">
              <td class="py-2 px-2 font-medium">{{ mat.name }}</td>
              <td class="py-2 px-2">
                <Badge v-if="mat.category?.name" variant="secondary">{{ mat.category.name }}</Badge>
                <span v-else>-</span>
              </td>
              <td class="py-2 px-2">{{ mat.quantity }}</td>
              <td class="py-2 px-2 flex gap-2">
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger as-child>
                      <Button variant="outline" size="icon" @click="openEditSheet(mat.id)">
                        <span class="sr-only">Editar</span>
                        <PencilIcon/>
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Editar</TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger as-child>
                      <Button variant="destructive" size="icon">
                        <span class="sr-only">Remover</span>
                        <TrashIcon/>
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Remover</TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <!-- Edit Sheet -->
                <Sheet :open="isEditSheetOpen(mat.id)" @update:open="val => { if (!val) closeEditSheet(); }">
                  <SheetContent side="right" class="w-full max-w-md">
                    <SheetHeader>
                      <SheetTitle>Editar Material</SheetTitle>
                    </SheetHeader>
                    <div class="mt-4 text-muted-foreground text-sm">[Formulário de edição para {{ mat.name }}]</div>
                  </SheetContent>
                </Sheet>
              </td>
            </tr>
            <tr v-if="props.materials.length === 0">
              <td colspan="4" class="text-center text-muted-foreground py-4">Sem materiais registados.</td>
            </tr>
          </tbody>
        </table>
      </Card>
    </div>
  </AppLayout>
</template>
