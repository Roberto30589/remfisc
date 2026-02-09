<script setup>
    import { onUnmounted, ref } from 'vue';
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import ApplicationMark from '@/Components/ApplicationMark.vue';
    import Banner from '@/Components/Banner.vue';
    import Dropdown from '@/Components/Dropdown.vue';
    import AsideDropdown from '@/Components/AsideDropdown.vue';
    import AsideDropdownLink from '@/Components/AsideDropdownLink.vue';
    import DropdownLink from '@/Components/DropdownLink.vue';
    import AsideLink from '@/Components/AsideLink.vue';
    
    import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
    import { faUser,faHouse,faTruck, faUsers, faGear} from '@fortawesome/free-solid-svg-icons'

    import ToastContainer from '@/Components/ToastContainer.vue';
    import toast from '@/Stores/toast';

    import { usePermission } from '@/Composables/permission';
    const { hasRole , hasPermission, hasPermissions } = usePermission();

    defineProps({
        title: String,
    });

    const showingNavigationDropdown = ref(false);

    const logout = () => {
        router.post(route('logout'));
    };

    //Esto es para los mensajes de success y error
    const page = usePage();

    let removeFinishEventListener = router.on('finish',()=>{
        if(page.props.flash?.success){
            console.log("success",page.props.flash.success);
            toast.add({type:"success",message:page.props.flash.success});
        }
        if(page.props.flash?.error){
            console.log("error",page.props.flash.error);
            toast.add({type:"error",message:page.props.flash.error});
        }
        
        if(page.props.errors){
            console.log("error",page.props.errors);
            Object.keys(page.props.errors).forEach(key => {
                toast.add({type:"error",message:page.props.errors[key]});
            });
        }
        if(page.props.flash?.warning){
            console.log("error",page.props.flash.warning);
            toast.add({type:"warning",message:page.props.flash.warning});
        }
        if(page.props.flash?.info){
            console.log("info",page.props.flash.info);
            toast.add({type:"info",message:page.props.flash.info});
        }
    });

    onUnmounted(()=> removeFinishEventListener());
</script>

<template>
    <Head :title="title" />
    <Banner />
    <nav class="fixed z-50 top-0 left-0 right-0  bg-[#202020] border-[#005856]">
        <!-- Primary Navigation Menu -->
        <div class="flex justify-between h-12">
            <div class="shrink-0 flex items-center justify-between sm:w-52">
                <Link :href="route('dashboard')">
                    <ApplicationMark class="block h-9 w-auto px-1 text-[#00be00]" />
                </Link>
                <span class="self-center text-lg font-serif whitespace-nowrap text-white px-1 hidden sm:inline">Remfisc</span>
                <!-- Hamburger -->
                <div class="flex items-center mb-1">
                    <button
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="inline-flex items-center justify-center rounded-md p-1 text-green-400 hover:ring-green-400 hover:ring-2 hover:text-green-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500 transition"
                        aria-label="Abrir menú de navegación"
                        :aria-expanded="showingNavigationDropdown.toString()"
                        aria-controls="mobile-menu"
                    >
                        <svg
                        :class="{ 'rotate-180 scale-110': showingNavigationDropdown }"
                        class="h-6 w-6 transition-transform duration-300 ease-in-out"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        >
                        <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center px-2">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button type="button" class="flex p-2 text-[#005856] bg-[#00be00] rounded-full ring-2 ring-emerald-600 focus:ring-2 focus:ring-gray-300" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                            <span class="sr-only">Open user menu</span>
                            <FontAwesomeIcon :icon="faUser" class="size-5"/>
                        </button>
                    </template>

                    <template #content class="my-1">
                        <!-- Account Management -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ $page.props.auth.user.name }}
                        </div>

                        <DropdownLink :href="route('profile.edit')">
                            Perfil
                        </DropdownLink>
                        <div class="border-t border-gray-200" />

                        <!-- Authentication -->
                        <form @submit.prevent="logout">
                            <DropdownLink as="button">
                                Cerrar sesión
                            </DropdownLink>
                        </form>
                    </template>
                </Dropdown>
            </div>
        </div>
    </nav>

    <aside class="fixed top-0 z-40 h-screen bg-[#002424] transition-all duration-300" :class="{'w-52 left-0 sm:w-10 sm:left-0': showingNavigationDropdown, 'w-52 -left-52 sm:w-52 sm:left-0': ! showingNavigationDropdown }">
        <div class="flex flex-col h-full pt-14">
            <!-- Navigation Links -->
            <AsideLink :href="route('dashboard')" :active="route().current('dashboard')">
                <FontAwesomeIcon :icon="faHouse" size="lg" class="min-w-6 pr-2" />
                Panel Inicial
            </AsideLink>
            
            <AsideDropdown :aside="showingNavigationDropdown" :active="route().current('admin.*')" :open="route().current('admin.*')">
                <template #trigger >
                    <FontAwesomeIcon :icon="faGear" size="lg" class="min-w-6 pr-2" />
                    Administración
                </template>

                <template #content>
                    <AsideDropdownLink :href="route('admin.users')" :active="route().current('admin.users')">
                        <FontAwesomeIcon :icon="faUsers" size="lg" class="min-w-7 pr-1" />
                        Usuarios
                    </AsideDropdownLink>
                    <AsideDropdownLink :href="route('admin.machines')" :active="route().current('admin.machines')">
                        <FontAwesomeIcon :icon="faTruck" size="lg" class="min-w-6 pr-2" />
                        Maquinas
                    </AsideDropdownLink>
                </template>
            </AsideDropdown>
        </div>
    </aside>

    <!-- HEADER SLOT -->
    <div v-if="$slots.header" class="pt-12">
        <div class="bg-white shadow py-6 px-4 sm:px-6 lg:px-8">
            <slot name="header" />
        </div>
    </div>

    <!-- Page Content -->
    <main class="pt-4 min-h-screen overflow-x-auto transition-margin-left duration-300"
          :class="{'ms-0 sm:ms-10': showingNavigationDropdown, 'ms-0 sm:ms-52': ! showingNavigationDropdown }">
        <slot />
    </main>

    <ToastContainer/>
</template>
