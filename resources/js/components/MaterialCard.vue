<script setup lang="ts">
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Clock, CheckCircle, XCircle } from 'lucide-vue-next';
import type { Material } from '@/types';

const props = defineProps<{
    material: Material
}>();

const getStatusInfo = (material: Material) => {
    if (material.isAvailable && material.availableQuantity > 0) {
        return {
            text: 'Disponível',
            icon: CheckCircle,
            color: 'text-green-600',
            bgColor: 'bg-green-100'
        };
    } else if (material.currentlyBorrowedQuantity > 0) {
        return {
            text: 'Emprestado',
            icon: Clock,
            color: 'text-orange-600',
            bgColor: 'bg-orange-100'
        };
    } else {
        return {
            text: 'Indisponível',
            icon: XCircle,
            color: 'text-gray-600',
            bgColor: 'bg-gray-100'
        };
    }
};

const status = getStatusInfo(props.material);
</script>

<template>
    <Card class="hover:shadow-lg transition-shadow border border-gray-200 dark:border-gray-700 rounded-2xl">
        <CardHeader class="pb-3">
            <div class="flex justify-between items-start">
                <div class="space-y-1">
                    <CardTitle class="text-lg font-semibold leading-tight">
                        {{ material.name }}
                    </CardTitle>
                    <p class="text-sm text-muted-foreground">
                        {{ material.category.name }}
                    </p>
                </div>
                <div
                    :class="[
            'flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium',
            status.bgColor,
            status.color
          ]"
                >
                    <component :is="status.icon" class="w-4 h-4" />
                    <span>{{ status.text }}</span>
                </div>
            </div>
        </CardHeader>

        <CardContent class="text-sm space-y-3 text-muted-foreground">
            <div class="border-t border-gray-100 dark:border-gray-700 pt-2">
                {{ material.description }}
            </div>
            <div class="flex-1"></div>
            <div class="flex flex-col gap-1">
                <div>
                    <span class="font-medium text-foreground">Disponível:</span>
                    {{ material.availableQuantity }} de {{ material.quantity }}
                </div>
                <div>
                    <span class="font-medium text-foreground">Máx. dias:</span>
                    {{ material.max_days_per_request }} dias
                </div>
            </div>
        </CardContent>

        <CardFooter>
            <Button
                v-if="material.isAvailable && material.availableQuantity > 0"
                @click="$emit('request', material)"
                class="w-full"
            >
                Requisitar
            </Button>
            <Button
                v-else
                variant="outline"
                disabled
                class="w-full"
            >
                {{ material.currentlyBorrowedQuantity > 0 ? 'Emprestado' : 'Indisponível' }}
            </Button>
        </CardFooter>
    </Card>
</template>

