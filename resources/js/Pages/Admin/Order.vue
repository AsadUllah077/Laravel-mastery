<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AcademyLayout from '@/Layouts/AcademyLayout.vue';
const props = defineProps({ module: Object });
const lessons = ref([...props.module.lessons]);
const form = useForm({ lesson_ids: [] });
function move(index, direction) { const next = index+direction; if(next>=0 && next<lessons.value.length) [lessons.value[index],lessons.value[next]] = [lessons.value[next],lessons.value[index]]; }
function save() { form.lesson_ids = lessons.value.map(lesson => lesson.id); form.put(route('admin.modules.reorder', props.module.id), { preserveScroll: true }); }
</script>
<template><Head title="Order lessons" /><AcademyLayout title="Module order"><div class="page-heading"><div><span class="eyebrow">ACADEMY STUDIO</span><h1>{{ module.title }}</h1><p>Use the arrows to build a natural learning sequence, then save the order.</p></div><Link :href="route('admin.index')" class="button secondary">Back to content</Link></div><form class="panel editor-section" @submit.prevent="save"><div v-for="(lesson,index) in lessons" :key="lesson.id" class="order-item"><strong>{{ String(index+1).padStart(2,'0') }}</strong><span>{{ lesson.title }}</span><div><button type="button" class="small-button" :disabled="index === 0" :aria-label="`Move ${lesson.title} up`" @click="move(index,-1)">↑</button><button type="button" class="small-button" :disabled="index === lessons.length-1" :aria-label="`Move ${lesson.title} down`" @click="move(index,1)">↓</button></div></div><p v-if="!lessons.length" class="muted">Add lessons to this module first.</p><button v-if="lessons.length" class="button primary mt-5" :disabled="form.processing">Save lesson order</button><p v-for="error in form.errors" :key="error" class="error-text">{{ error }}</p></form></AcademyLayout></template>
