<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import NotificationDescartes from '@/Components/NotificationDescartes.vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Descartes Notifications -->
        <NotificationDescartes />

        <!-- Sidebar Desktop -->
        <aside class="hidden md:flex flex-col w-64 bg-[#2d3436] text-white">
            <div class="p-6 flex items-center justify-center border-b border-gray-700">
                <Link :href="route('player.dashboard')">
                    <ApplicationLogo class="h-12 w-auto fill-current text-[#d65a31]" />
                </Link>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-2">
                <Link 
                    :href="route('player.dashboard')" 
                    class="flex items-center px-4 py-3 rounded-lg transition"
                    :class="route().current('player.dashboard') ? 'bg-[#d65a31] text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Tableau de bord
                </Link>
                
                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Gestion Ville</p>
                </div>

                <Link href="#" class="flex items-center px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Villes & Lieux
                </Link>

                <Link href="#" class="flex items-center px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Énigmes
                </Link>

                <Link :href="route('player.game.map')" class="flex items-center px-4 py-3 rounded-lg transition" :class="route().current('player.game.map') ? 'bg-[#d65a31] text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7m0 0L9 7"/></svg>
                    Vue Carte (Live)
                </Link>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Utilisateurs</p>
                </div>

                <Link href="#" class="flex items-center px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 01-9-3.5m9.936-3.464a4.45 4.45 0 11-8.928 0 4.45 4.45 0 018.928 0z"/></svg>
                    Joueurs & Équipes
                </Link>
            </nav>

            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center p-2 rounded-lg hover:bg-gray-800 transition">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs text-gray-500 truncate">Administrateur</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm z-10">
                <div class="px-4 h-16 flex items-center justify-between">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18" />
                        </svg>
                    </button>

                    <div class="flex-1 px-4 flex justify-between">
                        <h2 class="text-xl font-bold text-gray-800 leading-tight">
                            <slot name="header" />
                        </h2>

                        <div class="flex items-center space-x-4">
                            <Link :href="route('logout')" method="post" as="button" class="text-sm text-gray-500 hover:text-[#d65a31] transition font-medium">
                                Déconnexion
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <slot />
            </main>
        </div>
    </div>
</template>
