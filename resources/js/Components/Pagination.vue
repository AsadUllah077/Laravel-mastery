<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ links: Array });
function label(text) { return text.replace('&laquo;', '←').replace('&raquo;', '→'); }
</script>
<template><nav v-if="links?.length > 3" class="pagination" aria-label="Pagination"><template v-for="(link, i) in links" :key="i"><Link v-if="link.url" :href="link.url" :class="['page-link', { active: link.active }]" :aria-current="link.active ? 'page' : undefined">{{ label(link.label) }}</Link><span v-else class="page-link disabled">{{ label(link.label) }}</span></template></nav></template>
