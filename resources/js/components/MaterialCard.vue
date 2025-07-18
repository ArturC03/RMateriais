<script setup lang="ts">
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Box, CheckCircle, Clock, XCircle } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import {
  NumberField,
  NumberFieldContent,
  NumberFieldInput,
  NumberFieldIncrement,
  NumberFieldDecrement
} from '@/components/ui/number-field';
import type { Material, User } from '@/types';

const props = defineProps<{
    material: Material
}>();

const getStatusInfo = (material: Material) => {
    if (material.is_available && material.available_quantity > 0) {
        return {
            text: 'Disponível',
            icon: CheckCircle,
            color: 'text-green-600',
            dot: 'bg-green-500'
        };
    } else if (material.currently_borrowed_quantity > 0) {
        return {
            text: 'Emprestado',
            icon: Clock,
            color: 'text-orange-600',
            dot: 'bg-orange-400'
        };
    } else {
        return {
            text: 'Indisponível',
            icon: XCircle,
            color: 'text-gray-500',
            dot: 'bg-gray-400'
        };
    }
};

const status = getStatusInfo(props.material);
const quantity = ref(1);
const loading = ref(false);

// Reset quantity to 1 if material changes or available_quantity changes
watch(() => props.material.id, () => { quantity.value = 1; });
watch(() => props.material.available_quantity, () => { quantity.value = 1; });


function handleAddToCart() {
  // Clamp quantity
  const q = Math.max(1, Math.min(quantity.value, props.material.available_quantity));
  quantity.value = q;

  // Try to get user from inject (provided by parent), fallback to event
  const user : User = usePage().props.auth?.user;
  if (!user) {
    toast.error('Precisas estar autenticado(a) para adicionar ao carrinho.');
    return;
  }
  if (!props.material) {
    toast.error('Nenhum material selecionado.');
    return;
  }
  loading.value = true;
  router.post('/materiais/adicionar-ao-carrinho', {
    material_id: props.material.id,
    quantity: q,
    days: props.material.max_days_per_request
  }, {
    onSuccess: () => {
      toast.success('Material adicionado ao carrinho com sucesso!');
      loading.value = false;
    },
    onError: (errors) => {
      if (errors.error) {
        toast.error(errors.error);
      } else {
        toast.error('Erro ao adicionar ao carrinho.');
      }
      loading.value = false;
    },
    preserveScroll: true,
  });
}
</script>

<template>
  <Card class="flex flex-col items-stretch rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm bg-white dark:bg-zinc-900 p-0 overflow-hidden">
    <!-- Image/Icon Placeholder -->
    <div class="flex items-center justify-center bg-gray-100 dark:bg-zinc-800 h-24 w-full">
      <Box class="w-10 h-10 text-gray-400" />
    </div>
    <CardContent class="flex flex-col gap-2 px-4 pt-3 pb-2 flex-1">
      <!-- Title -->
      <div class="font-semibold text-base text-foreground truncate">{{ material.name }}</div>
      <!-- Category Badge -->
      <div v-if="material.category?.name" class="mt-1">
        <Badge variant="secondary">{{ material.category.name }}</Badge>
      </div>
      <!-- Description -->
      <div class="text-sm text-muted-foreground truncate mb-1" :title="material.description">
        {{ material.description }}
      </div>
      <!-- Divider -->
      <div class="border-t border-gray-100 dark:border-gray-700 my-1" />
      <!-- Info Row -->
      <div class="flex items-center justify-between text-xs mt-1">
        <div>
          <span class="font-medium text-foreground">Disponível:</span>
          {{ material.available_quantity }} de {{ material.quantity }}
        </div>
        <div>
          <span class="font-medium text-foreground">Tempo:</span>
          {{ material.max_days_per_request }}d
        </div>
      </div>
      <!-- Status Badge -->
      <div class="flex items-center gap-1 mt-1">
        <span :class="['inline-block w-2 h-2 rounded-full', status.dot]" />
        <span :class="['text-xs', status.color]">{{ status.text }}</span>
      </div>
    </CardContent>
    <CardFooter class="px-4 pb-3 pt-2">
      <div class="flex items-center gap-2 w-full">
        <Button
          :disabled="!material.is_available || material.available_quantity === 0 || loading"
          @click="handleAddToCart"
          class="flex-1"
        >
          {{ loading ? 'A adicionar...' : 'Adicionar ao carrinho' }}
        </Button>
        <NumberField
          v-model="quantity"
          :min="1"
          :max="material.available_quantity"
          :disabled="!material.is_available || material.available_quantity === 0 || loading"
          class="w-20"
        >
          <NumberFieldContent>
            <NumberFieldDecrement />
            <NumberFieldInput />
            <NumberFieldIncrement />
          </NumberFieldContent>
        </NumberField>
      </div>
      <Button
        v-if="!material.is_available || material.available_quantity === 0"
        variant="outline"
        disabled
        class="w-full mt-2"
      >
        {{ material.currently_borrowed_quantity > 0 ? 'Emprestado' : 'Indisponível' }}
      </Button>
    </CardFooter>
  </Card>
</template>

