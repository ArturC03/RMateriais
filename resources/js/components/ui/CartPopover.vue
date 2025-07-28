<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { LoaderCircle, ShoppingCart, Trash, ExternalLink } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import type { Auth, Request, RequestItem } from '@/types';
import { toast } from 'vue-sonner';

const page = usePage();
const props = computed(() => page.props as { auth?: Auth; cart?: Request });
const user = computed(() => props.value.auth?.user);

const items = computed(() => Array.isArray(props.value.cart?.request_items) ? props.value.cart.request_items : []);
const localItems = ref<RequestItem[]>([...items.value]);
const removingItemId = ref<number | null>(null);
const finalizingRequest = ref<boolean>(false);

watch(items, (newItems) => {
  localItems.value = [...newItems];
}, { immediate: true });

function removeItem(item: RequestItem) {
  if (!user.value) {
    toast.error('Precisas estar autenticado para remover do carrinho.');
    return;
  }
  removingItemId.value = item.id;
  router.post('materiais/remover-do-carrinho', {
    material_id: item.material?.id,
  }, {
    onSuccess: () => {
      const idx = localItems.value.findIndex(i => i.id === item.id);
      if (idx !== -1) localItems.value.splice(idx, 1);
      toast.success('Item removido do carrinho!');
      removingItemId.value = null;
    },
    onError: (errors: any) => {
      toast.error(errors.error || 'Erro ao remover do carrinho.');
      removingItemId.value = null;
    },
    preserveScroll: true,
  });
}

function finalizeRequest() {
  finalizingRequest.value = true;
  if (!user.value) {
    toast.error('Precisas estar autenticado para finalizar o pedido.');
    return;
  }
  router.post('requisicao/fazer-pedido', {}, {
    onSuccess: () => {
      toast.success('Pedido realizado!');
      finalizingRequest.value = false;
      localItems.value = [];
    },
    onError: (errors: any) => {
      toast.error(errors.error || 'Erro ao finalizar pedido.');
      finalizingRequest.value = false;
    },
    preserveScroll: true,
  });
}

const isEmpty = computed(() => localItems.value.length === 0);
</script>

<template>

    <TooltipProvider :delay-duration="0">
        <Tooltip>
            <TooltipTrigger>
                <Popover>
                    <PopoverTrigger as-child>
                        <Button variant="ghost" size="icon" class="relative">
                            <ShoppingCart class="size-5 opacity-80 group-hover:opacity-100" />
                            <span v-if="user && localItems.length > 0" class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full px-1.5 py-0.5">{{ localItems.length }}</span>
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-80 p-4 relative">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-lg">Carrinho</h3>
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Link href="/carrinho">
                                                <ExternalLink class="size-5 opacity-70 hover:opacity-100 transition cursor-pointer" />
                                            </Link>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            Ver carrinho completo
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>
                            <div v-if="!user">
                                <div class="text-center text-muted-foreground py-6">
                                    <ShoppingCart class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                    <div>Inicie sessão para usar o carrinho.</div>
                                </div>
                            </div>
                            <div v-else-if="isEmpty" class="text-center text-muted-foreground py-6">
                                <ShoppingCart class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                <div>O seu carrinho está vazio</div>
                            </div>
                            <div v-else>
                                <ul class="divide-y divide-border max-h-48 overflow-y-auto mb-2">
                                    <li v-for="item in localItems" :key="item.id" class="flex items-center justify-between py-2">
                                        <div>
                                            <div class="font-medium">{{ item.material?.name || '-' }}</div>
                                            <div class="text-xs text-muted-foreground">Qtd: {{ item.quantity }} | Duração: 3 dias</div>
                                        </div>
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            class="text-red-600 border-red-200 hover:bg-red-50 ml-2"
                                            :disabled="removingItemId === item.id"
                                            @click="removeItem(item)"
                                        >
                                            <LoaderCircle v-if="removingItemId === item.id" class="h-4 w-4 animate-spin" />
                                            <Trash v-else class="h-4 w-4 opacity-80" />
                                        </Button>
                                    </li>
                                </ul>
                                <div class="flex flex-col gap-2">
                                    <Button block size="sm" @click="finalizeRequest" :disabled="finalizingRequest">
                                        <LoaderCircle v-if="finalizingRequest" class="h-4 w-4 animate-spin mr-2" />
                                        {{ finalizingRequest ? 'A Finalizar...' : 'Finalizar Pedido' }}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>
            </TooltipTrigger>
            <TooltipContent>
                <p>Carrinho</p>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>

</template>

<style scoped>
</style>
