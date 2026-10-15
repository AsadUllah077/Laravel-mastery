<script setup>
import { reactive } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AcademyLayout from '@/Layouts/AcademyLayout.vue';
import LessonCard from '@/Components/LessonCard.vue';
import Pagination from '@/Components/Pagination.vue';
import Icon from '@/Components/AcademyIcon.vue';
const props = defineProps({ lessons: Object, filters: Object, modules: Array, tags: Array });
const filters = reactive({ q: props.filters.q || '', module: props.filters.module || '', difficulty: props.filters.difficulty || '', tag: props.filters.tag || '', bookmarked: props.filters.bookmarked ? 1 : '' });
function search() { router.get(route('lessons.index'), Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '')), { preserveState: true, replace: true }); }
</script>
<template>
    <Head title="Lesson library" />
    <AcademyLayout title="Lesson library">
        <div class="page-heading"><div><span class="eyebrow">THE DEVELOPER'S TOOLBOX</span><h1>{{ filters.bookmarked ? 'Your saved discoveries.' : 'One lesson closer to mastery.' }}</h1><p>Build real understanding, from your first PHP class to production Laravel.</p></div><span class="count-badge">{{ lessons.total }} lessons</span></div>
        <form class="library-filters panel" @submit.prevent="search">
            <label class="search-field"><Icon name="search" size="19" /><input v-model="filters.q" placeholder="Search topics, code, and concepts…" aria-label="Search lessons" maxlength="100" /></label>
            <div class="filter-row"><select v-model="filters.module" aria-label="Module" @change="search"><option value="">All modules</option><option v-for="module in modules" :key="module.id" :value="module.id">{{ module.title }}</option></select><select v-model="filters.difficulty" aria-label="Difficulty" @change="search"><option value="">All levels</option><option>beginner</option><option>intermediate</option><option>advanced</option></select><select v-model="filters.tag" aria-label="Tag" @change="search"><option value="">All tags</option><option v-for="tag in tags" :key="tag.id" :value="tag.id">{{ tag.name }}</option></select><label class="checkbox-label"><input v-model="filters.bookmarked" type="checkbox" true-value="1" false-value="" @change="search" /> Bookmarked</label><button class="button primary">Search <Icon name="arrow" size="16" /></button></div>
        </form>
        <div class="library-result-label"><span>{{ lessons.total }} {{ lessons.total === 1 ? 'lesson' : 'lessons' }} to explore</span><Link :href="route('lessons.index')" class="text-link">Clear filters</Link></div>
        <div v-if="lessons.data.length" class="lesson-grid"><LessonCard v-for="lesson in lessons.data" :key="lesson.id" :lesson="lesson" /></div>
        <div v-else class="empty-state panel"><Icon name="search" size="34" /><h2>No lessons found</h2><p>Try another keyword or explore all modules.</p><Link :href="route('lessons.index')" class="button secondary">Explore lessons</Link></div>
        <Pagination :links="lessons.links" />
    </AcademyLayout>
</template>
