<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
</script>
<template>
    <Head title="RMateriais">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>


    <div class="min-h-screen bg-background text-foreground flex flex-col">

        <!-- Navbar -->
        <nav class="border-b border-border bg-card">
            <div class="container mx-auto px-4 py-2">
                <div class="flex items-center justify-between        console.log($props);">
                    <div class="flex items-center space-x-3">
                        <AppLogo />
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link
                            v-if="$page.props.auth.user.role === 'admin'"
                            :href="route('dashboard')"
                            class="text-sm text-muted-foreground hover:text-foreground"
                        >
                            Dashboard
                        </Link>
                        <template v-else-if="$page.props.auth.user.role === 'aluno'">
                            <Button as-child>
                                <Link :href="route('requisitions.index')">
                                    Requisitar
                                </Link>
                            </Button>
                        </template>
                        <template v-else>
                            <Button variant="link" class="text-sm m-2" :href="route('login')" as-child>
                                <Link :href="route('login')">Entrar</Link>
                            </Button>
                            <Button as-child>
                                <Link :href="route('register')">
                                    Registar
                                </Link>
                            </Button>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center">
            <div class="container mx-auto px-4 py-8">
                <div class="max-w-md mx-auto text-center">
                    <Heading
                        title="Bem-vindo ao RMateriais"
                        description="Plataforma para empréstimo de materiais académicos"
                    />
                    <Button as-child size="lg">
                        <Link :href="route('login')">
                            Entrar
                        </Link>
                    </Button>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-border bg-card">
            <div class="container mx-auto px-4 py-6">
                <div class="text-center">
                    <p class="text-sm text-muted-foreground">
                        © 2024 RMateriais - Prof. Ruben
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
