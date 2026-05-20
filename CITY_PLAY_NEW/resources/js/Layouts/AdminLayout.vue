<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import PrimeModalManager from '@/Components/PrimeModalManager.vue';
import { Link } from '@inertiajs/vue3';
import NotificationDescartes from '@/Components/NotificationDescartes.vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#FDFBF7] text-[#2D1B16] flex flex-col md:flex-row antialiased">
        <NotificationDescartes />

        <header class="md:hidden bg-[#FDFBF7] text-[#2D1B16] px-4 h-16 flex items-center justify-between shadow-[0_16px_40px_-30px_rgba(224,83,28,0.7)] border-b border-[#E0531C]/15 z-40">
            <div class="flex items-center gap-3">
                <Link :href="route('player.dashboard')" class="bg-[#FFE9D1] p-1.5 rounded-2xl border border-[#E0531C]/20 flex items-center justify-center shadow-sm">
                    <ApplicationLogo class="h-7 w-auto fill-current text-[#E0531C]" />
                </Link>
                <span class="font-sans font-black text-xs uppercase tracking-wider">CityPlay <span class="text-[#FFB700]">Régisseur</span></span>
            </div>
            
            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="p-2.5 bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white rounded-2xl active:scale-95 transition-all duration-300 focus:outline-none shadow-md shadow-[#E0531C]/20">
                <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        <aside :class="[showingNavigationDropdown ? 'translate-x-0' : '-translate-x-full md:translate-x-0']" 
               class="fixed inset-y-0 left-0 w-64 bg-[#FFF7EB] text-[#2D1B16] flex flex-col justify-between z-30 border-r border-[#E0531C]/10 transition-transform duration-300 md:sticky md:h-screen shadow-xl pt-16 md:pt-0">
            
            <div>
                <div class="hidden md:flex p-5 border-b border-[#E0531C]/15 bg-[#FFF3DF] items-center gap-3">
                    <Link :href="route('player.dashboard')" class="w-10 h-10 rounded-3xl bg-[#FFE5C2] border border-[#E0531C]/25 flex items-center justify-center shadow-sm group transition-transform duration-300 hover:-translate-y-0.5">
                        <ApplicationLogo class="h-6 w-auto fill-current text-[#E0531C] group-hover:scale-105 transition-transform" />
                    </Link>
                    <div>
                        <h1 class="font-sans font-black text-[13px] text-[#2D1B16] uppercase tracking-wider m-0 leading-tight">
                            Jeu de Rôle <span class="text-[#E0531C]">Urbain</span>
                        </h1>
                        <span class="text-[9px] font-bold text-[#B86A16]/80 uppercase tracking-widest block mt-0.5">Espace Régisseur</span>
                    </div>
                </div>

                <nav class="p-4 flex flex-col gap-3 overflow-visible">
                    <Link 
                        :href="route('player.dashboard')" 
                        class="flex items-center gap-3 px-4 py-3 rounded-3xl text-xs uppercase tracking-wider transition-all duration-300 group border border-transparent shadow-sm active:scale-95"
                        :class="route().current('player.dashboard') 
                            ? 'bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white font-black shadow-[0_16px_40px_-28px_rgba(224,83,28,0.8)] border-[#FFB700]' 
                            : 'bg-white/80 text-[#5C4033] hover:bg-[#FFE8D5] hover:text-[#2D1B16] font-bold'"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Tableau de bord</span>
                    </Link>
                    
                    <span class="text-[9px] font-black uppercase tracking-widest text-[#B86A16]/70 px-4 mt-4 mb-1.5 block">Gestion Cité</span>
                    
                    <Link 
                        :href="route('admin.cities.index')" 
                        class="flex items-center gap-3 px-4 py-3 rounded-3xl text-xs uppercase tracking-wider transition-all duration-300 group border border-transparent shadow-sm active:scale-95"
                        :class="route().current('admin.cities.*') 
                            ? 'bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white font-black shadow-[0_16px_40px_-28px_rgba(224,83,28,0.8)] border-[#FFB700]' 
                            : 'bg-white/80 text-[#5C4033] hover:bg-[#FFE8D5] hover:text-[#2D1B16] font-bold'"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Parcours & Villes</span>
                    </Link>

                    <Link 
                        :href="route('admin.places.index')" 
                        class="flex items-center gap-3 px-4 py-3 rounded-3xl text-xs uppercase tracking-wider transition-all duration-300 group border border-transparent shadow-sm active:scale-95"
                        :class="route().current('admin.places.*') || route().current('admin.riddles.*')
                            ? 'bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white font-black shadow-[0_16px_40px_-28px_rgba(224,83,28,0.8)] border-[#FFB700]' 
                            : 'bg-white/80 text-[#5C4033] hover:bg-[#FFE8D5] hover:text-[#2D1B16] font-bold'"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Lieux & Énigmes</span>
                    </Link>

                    <span class="text-[9px] font-black uppercase tracking-widest text-[#B86A16]/70 px-4 mt-4 mb-1.5 block">Communauté</span>

                    <Link 
                        :href="route('admin.users.index')" 
                        class="flex items-center gap-3 px-4 py-3 rounded-3xl text-xs uppercase tracking-wider transition-all duration-300 group border border-transparent shadow-sm active:scale-95"
                        :class="route().current('admin.users.*') 
                            ? 'bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white font-black shadow-[0_16px_40px_-28px_rgba(224,83,28,0.8)] border-[#FFB700]' 
                            : 'bg-white/80 text-[#5C4033] hover:bg-[#FFE8D5] hover:text-[#2D1B16] font-bold'"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 01-9-3.5m9.936-3.464a4.45 4.45 0 11-8.928 0 4.45 4.45 0 018.928 0z"/></svg>
                        <span>Joueurs & Équipes</span>
                    </Link>
                </nav>
            </div>

            <div class="p-4 border-t border-[#E0531C]/15 bg-[#FFF7EB] flex items-center justify-between">
                <div class="flex items-center gap-2.5 truncate">
                    <div class="w-8 h-8 rounded-2xl bg-[#E0531C]/10 border border-[#E0531C]/20 flex items-center justify-center text-xs font-black text-[#E0531C] uppercase shadow-inner shrink-0">
                        {{ $page.props.auth.user.name[0] }}
                    </div>
                    <div class="truncate">
                        <span class="block text-xs font-black text-[#2D1B16] leading-tight truncate">{{ $page.props.auth.user.name }}</span>
                        <span class="block text-[9px] font-bold text-[#B86A16]/70 uppercase tracking-wide mt-0.5">Administrateur</span>
                    </div>
                </div>
                
                <Link :href="route('logout')" method="post" as="button" class="p-2 rounded-2xl bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white hover:brightness-110 transition-all duration-300 active:scale-95 focus:outline-none" title="Déconnexion">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </Link>
            </div>
        </aside>

        <div v-if="showingNavigationDropdown" @click="showingNavigationDropdown = false" class="fixed inset-0 bg-[#2D1B16]/15 backdrop-blur-sm z-20 md:hidden"></div>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="hidden md:block bg-[#FDFBF7] border-b border-[#E0531C]/10 shadow-sm z-10 shrink-0">
                <div class="px-8 h-16 flex items-center justify-between">
                    <h2 class="font-sans text-lg font-black text-[#2D1B16] tracking-tight">
                        <slot name="header" />
                    </h2>

                    <div class="flex items-center space-x-4">
                        <Link :href="route('logout')" method="post" as="button" class="text-xs uppercase font-black tracking-wider text-[#5C4033]/70 hover:text-[#E0531C] transition-colors focus:outline-none">
                            Déconnexion
                        </Link>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-[#FDFBF7]">
                <slot />
            </main>
        </div>

        <PrimeModalManager />
    </div>
</template>

