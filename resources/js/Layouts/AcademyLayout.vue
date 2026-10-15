<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/AcademyIcon.vue';
const props = defineProps({ title: { type: String, default: 'Overview' }, eyebrow: { type: String, default: 'YOUR WORKSPACE' } });
const page = usePage();
const user = computed(() => page.props.auth.user);
const mobileOpen = ref(false), collapsed = ref(false), dark = ref(false), search = ref('');
const active = (name) => route().current(name);
const links = [{ name: 'dashboard', label: 'Overview', icon: 'home' }, { name: 'lessons.index', label: 'Lesson library', icon: 'book' }, { name: 'history', label: 'My progress', icon: 'gauge' }];
function searchLibrary() { if (search.value.trim()) router.get(route('lessons.index'), { q: search.value.trim() }); }
function toggleTheme() { dark.value = !dark.value; document.documentElement.classList.toggle('dark', dark.value); try { localStorage.setItem('academy-theme', dark.value ? 'dark' : 'light'); } catch {} }
function keydown(event) { if ((event.ctrlKey || event.metaKey) && event.key === 'k') { event.preventDefault(); document.getElementById('global-search')?.focus(); } if (event.key === 'Escape') mobileOpen.value = false; }
let removeNavigate;
onMounted(() => { dark.value = document.documentElement.classList.contains('dark'); document.addEventListener('keydown', keydown); removeNavigate = router.on('navigate', () => mobileOpen.value = false); });
onUnmounted(() => { document.removeEventListener('keydown', keydown); removeNavigate?.(); });
</script>
<template>
    <div :class="['academy-shell', { 'sidebar-collapsed': collapsed }]">
        <a href="#main-content" class="skip-link">Skip to content</a>
        <button v-if="mobileOpen" class="sidebar-overlay" aria-label="Close navigation" @click="mobileOpen = false" />
        <aside :class="['sidebar', { 'mobile-open': mobileOpen }]" aria-label="Main navigation">
            <Link :href="route('dashboard')" class="brand"><span class="brand-mark">L<span>∕</span></span><span class="collapse-hide"><strong>Laravel Mastery<span class="brand-dot">.</span></strong><small>ACADEMY</small></span></Link>
            <div class="workspace-label collapse-hide"><span class="workspace-dot" /> Personal workspace <span>⌄</span></div>
            <div class="nav-label collapse-hide">LEARN & BUILD</div>
            <nav class="primary-nav">
                <Link v-for="link in links" :key="link.name" :href="route(link.name)" :class="{ selected: active(link.name) }" :title="link.label"><Icon :name="link.icon" /><span class="collapse-hide">{{ link.label }}</span><span v-if="active(link.name)" class="nav-active-dot collapse-hide" /></Link>
                <Link :href="route('lessons.index', { bookmarked: 1 })" :class="{ selected: active('lessons.index') && page.props.filters?.bookmarked }" title="Bookmarks"><Icon name="bookmark" /><span class="collapse-hide">Bookmarks</span></Link>
            </nav>
            <div class="nav-label collapse-hide">YOUR LEARNING PATH</div>
            <nav class="path-nav collapse-hide">
                <Link v-for="module in page.props.navigationModules || []" :key="module.id" :href="route('lessons.index', { module: module.id })"><span class="path-number">{{ String(module.position + 1).padStart(2, '0') }}</span>{{ module.title }}</Link>
            </nav>
            <div class="sidebar-bottom">
                <div class="sidebar-note collapse-hide"><div class="note-icon"><Icon name="rocket" size="21" /></div><strong>A little progress, every day.</strong><p>Great developers are built one lesson at a time.</p><Link :href="route('lessons.index')">Explore your next lesson <span>↗</span></Link></div>
                <Link v-if="user.is_admin" :href="route('admin.index')" class="sidebar-utility" title="Content administration"><Icon name="shield" /><span class="collapse-hide">Content administration</span></Link>
                <Link :href="route('profile.edit')" class="sidebar-utility" title="Settings"><Icon name="settings" /><span class="collapse-hide">Settings & account</span></Link>
                <button class="sidebar-utility collapse-button" @click="collapsed = !collapsed" :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"><Icon name="menu" /><span class="collapse-hide">Collapse sidebar</span></button>
                <div class="sidebar-profile"><span class="avatar">{{ user.name.slice(0, 2).toUpperCase() }}</span><span class="collapse-hide"><strong>{{ user.name }}</strong><small>{{ user.is_admin ? 'Academy administrator' : 'Lifelong learner' }}</small></span><Link class="logout" :href="route('logout')" method="post" as="button" title="Sign out"><Icon name="logout" size="17" /></Link></div>
            </div>
        </aside>
        <div class="workspace-main">
            <header class="topbar">
                <button class="icon-button mobile-toggle" aria-label="Open navigation" @click="mobileOpen = true"><Icon name="menu" /></button>
                <div class="topbar-breadcrumb"><span>Workspace</span><Icon name="chevron" size="12" /><strong>{{ title }}</strong></div>
                <form class="global-search" role="search" @submit.prevent="searchLibrary"><Icon name="search" size="17" /><input id="global-search" v-model="search" aria-label="Search all lessons" placeholder="Search anything…" maxlength="100" /><kbd>Ctrl K</kbd></form>
                <button class="icon-button theme-toggle" :aria-label="dark ? 'Use light theme' : 'Use dark theme'" @click="toggleTheme"><Icon :name="dark ? 'sun' : 'moon'" size="19" /></button>
                <Link :href="route('profile.edit')" class="avatar top-avatar" aria-label="Your profile">{{ user.name.slice(0, 2).toUpperCase() }}</Link>
            </header>
            <main id="main-content" class="page-content">
                <div v-if="page.props.flash?.success" role="status" class="flash-message"><Icon name="check" size="18" />{{ page.props.flash.success }}</div>
                <slot />
                <footer class="page-footer"><span>Made for curious minds. Built with Laravel.</span><span>LEARN. PRACTICE. MASTER.</span></footer>
            </main>
        </div>
    </div>
</template>
