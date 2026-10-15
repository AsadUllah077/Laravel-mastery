<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AcademyLayout from '@/Layouts/AcademyLayout.vue';
import Pagination from '@/Components/Pagination.vue';
defineProps({ tags: Object });
const editing = ref(null), form = useForm({ name: '' });
function edit(tag) { editing.value = tag; form.name = tag.name; form.clearErrors(); }
function save() { const options = { preserveScroll: true, onSuccess: () => { editing.value = null; form.reset(); } }; editing.value ? form.put(route('admin.tags.update', editing.value.id), options) : form.post(route('admin.tags.store'), options); }
function remove(tag) { if (confirm(`Remove the tag “${tag.name}” from all lessons?`)) router.delete(route('admin.tags.destroy', tag.id), { preserveScroll: true }); }
</script>
<template><Head title="Manage tags" /><AcademyLayout title="Content tags"><div class="page-heading"><div><span class="eyebrow">ACADEMY STUDIO</span><h1>Make every concept discoverable.</h1><p>Create and rename tags, or remove their assignments across lessons.</p></div><Link :href="route('admin.index')" class="button secondary">Back to content</Link></div><form class="panel editor-section" @submit.prevent="save"><h2 class="mb-4">{{ editing ? 'Rename tag' : 'Create tag' }}</h2><label class="field">Tag name<input v-model="form.name" maxlength="50" required /></label><p v-if="form.errors.name" class="error-text mb-3">{{ form.errors.name }}</p><div class="flex gap-3"><button class="button primary" :disabled="form.processing">Save tag</button><button v-if="editing" type="button" class="button secondary" @click="editing = null; form.reset()">Cancel</button></div></form><section class="panel admin-list-panel"><div v-for="tag in tags.data" :key="tag.id" class="module-admin-row"><span><strong>#{{ tag.name }}</strong><small>{{ tag.lessons_count }} lessons</small></span><div><button class="small-button" @click="edit(tag)">Rename</button><button class="small-button danger" @click="remove(tag)">Remove</button></div></div><Pagination :links="tags.links" /></section></AcademyLayout></template>
