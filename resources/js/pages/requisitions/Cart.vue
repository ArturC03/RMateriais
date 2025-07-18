<script setup lang="ts">
import { usePage , router} from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import type { Auth, Request, RequestItem } from '@/types';
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import Icon from '@/components/Icon.vue';
import {
  Table,
  TableHeader,
  TableHead,
  TableBody,
  TableRow,
  TableCell,
  TableFooter
} from '@/components/ui/table';
import { LoaderCircle } from 'lucide-vue-next';

const page = usePage();
const props = computed(() => page.props as {
    auth?: Auth;
    cart?: Request;
});

const removingItemId = ref<number | null>(null);
// Use a local ref for items to allow immediate UI updates
const items = ref<RequestItem[]>([...(props.value.cart?.request_items ?? [])]);
const totalItems = computed(() => items.value.length);
const isEmpty = computed(() => totalItems.value === 0);

function removeItem(item: RequestItem) {
  removingItemId.value = item.id;

  // Call the backend to remove the item from the cart
  router.post('materiais/remover-do-carrinho', {
    material_id: item.material?.id,
  }, {
    onSuccess: () => {
      const idx = items.value.findIndex(i => i.id === item.id);

      if (idx !== -1)
        items.value.splice(idx, 1);

      toast.success('Item removido do carrinho!');
      removingItemId.value = null;
    },
    onError: (errors: any) => {
      if (errors.error) {
        toast.error(errors.error);
      } else {
        toast.error('Erro ao remover do carrinho.');
      }
      removingItemId.value = null;
    },
    preserveScroll: true,
  });
}

function finalizeRequest() {
  // TODO: Replace with actual API call
  toast.success('Pedido finalizado (placeholder)');
}

</script>

<template>
  <Head title="Carrinho de Materiais" />
  <AppLayout>
    <div class="container mx-auto px-4 py-6">
      <div class="mb-8">
        <Heading
          title="Carrinho de Materiais"
          description="Revise os materiais que pretende requisitar antes de finalizar o pedido."
        />
      </div>

      <div v-if="isEmpty" class="text-center py-12">
        <div class="text-muted-foreground">
          <Icon name="shopping-cart" class="h-12 w-12 mx-auto mb-4 opacity-50" />
          <h3 class="text-lg font-medium mb-2">O seu carrinho está vazio</h3>
          <p>Adicione materiais ao carrinho para fazer um pedido.</p>
        </div>
      </div>

      <div v-else>
        <Table class="rounded-lg border border-border bg-card">
          <TableHeader>
            <TableRow>
              <TableHead class="px-4 py-3">Material</TableHead>
              <TableHead class="px-4 py-3">Categoria</TableHead>
              <TableHead class="px-4 py-3 text-center">Quantidade</TableHead>
              <TableHead class="px-4 py-3 text-center">Dias</TableHead>
              <TableHead class="px-4 py-3 text-center">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in items" :key="item.id">
              <TableCell class="px-4 py-3">
                <div class="font-medium text-foreground">{{ item.material?.name || '-' }}</div>
                <div class="text-xs text-muted-foreground">{{ item.material?.description || '' }}</div>
              </TableCell>
              <TableCell class="px-4 py-3">{{ item.material?.category?.name || '-' }}</TableCell>
              <TableCell class="px-4 py-3 text-center">{{ item.quantity }}</TableCell>
              <TableCell class="px-4 py-3 text-center">{{ item.requested_days }}</TableCell>
              <TableCell class="px-4 py-3 text-center">
                <Button
                  variant="outline"
                  size="sm"
                  class="text-red-600 border-red-200 hover:bg-red-50"
                  :disabled="removingItemId === item.id"
                  @click="removeItem(item)"
                >
                  <LoaderCircle v-if="removingItemId === item.id" class="h-4 w-4 animate-spin mr-1" />
                  <Icon v-else name="trash" class="h-4 w-4 mr-1" />
                  Remover
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
          <TableFooter>
            <TableRow>
              <TableCell colspan="2" class="px-4 py-3 font-semibold text-right">Total de itens:</TableCell>
              <TableCell class="px-4 py-3 text-center font-semibold">{{ totalItems }}</TableCell>
              <TableCell colspan="2"></TableCell>
            </TableRow>
          </TableFooter>
        </Table>
        <div class="flex flex-col md:flex-row md:items-center md:justify-end gap-4 mt-8">
          <Button size="lg" class="w-full md:w-auto" @click="finalizeRequest">
            Finalizar Pedido
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
</style>
