<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuLabel,
    DropdownMenuSeparator
} from '@/components/ui/dropdown-menu';
import { Search, Filter } from 'lucide-vue-next';
import MaterialCard from '@/components/MaterialCard.vue';
import type { Category, Material, User } from '@/types';
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    materials: Material[];
    categories: Category[];
}>();

const searchQuery = ref('');
const selectedCategory = ref('Todos');

const categoryNames = computed(() => ['Todos', ...props.categories.map(c => c.name)]);

const filteredMaterials = computed(() => {
    let filtered = props.materials;

    if (selectedCategory.value !== 'Todos') {
        filtered = filtered.filter(mat => mat.category?.name === selectedCategory.value);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(mat =>
            mat.name.toLowerCase().includes(query) ||
            mat.description.toLowerCase().includes(query)
        );
    }

    return filtered;
});

const page = usePage();
const user = computed(() => (page.props as any)?.auth?.user as User | undefined);

function handleAddToCart(material: Material, quantity: number) {
    if (!user.value) {
        toast.error('Precisas estar autenticado para adicionar ao carrinho.');
        return;
    }
    if (!material) {
        toast.error('Nenhum material selecionado.');
        return;
    }
    router.post('/materiais/adicionar-ao-carrinho', {
        material_id: material.id,
        quantity: quantity,
    }, {
        onSuccess: () => {
            toast.success('Material adicionado ao carrinho com sucesso!');
        },
        onError: (errors) => {
            if (errors.error) {
                toast.error(errors.error);
            } else {
                toast.error('Erro ao adicionar ao carrinho.');
            }
        },
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Requisitar Materiais" />
<AppLayout>
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-8">
            <Heading
                title="Requisitar Materiais"
                description="Pesquisa e encontra os materiais disponíveis para empréstimo"
            />
        </div>

        <!-- Search and Filter Section -->
        <div class="mb-6 space-y-4">
            <!-- Search Bar -->
            <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                <Input
                    v-model="searchQuery"
                    placeholder="Pesquisar materiais..."
                    class="pl-10"
                />
            </div>

            <!-- Filter Row -->
            <div class="flex items-center justify-between">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" class="flex items-center gap-2">
                            <Filter class="h-4 w-4" />
                            {{ selectedCategory }}
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuLabel>Categorias</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            v-for="category in categoryNames"
                            :key="category"
                            @click="selectedCategory = category"
                            :class="{ 'bg-accent': selectedCategory === category }"
                        >
                            {{ category }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <div class="text-sm text-muted-foreground">
                    {{ filteredMaterials.length }} material(is) encontrado(s)
                </div>
            </div>
        </div>

        <!-- Materials Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <MaterialCard
                v-for="material in filteredMaterials"
                :key="material.id"
                :material="material"
                @request="handleAddToCart"
            />
        </div>

        <!-- Empty State -->
        <div
            v-if="filteredMaterials.length === 0"
            class="text-center py-12"
        >
            <div class="text-muted-foreground">
                <Search class="h-12 w-12 mx-auto mb-4 opacity-50" />
                <h3 class="text-lg font-medium mb-2">Nenhum material encontrado</h3>
                <p>Tente ajustar os filtros ou a pesquisa</p>
            </div>
        </div>
    </div>
</AppLayout>
</template>
