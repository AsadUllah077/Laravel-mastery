<script setup>
import { Link, router } from '@inertiajs/vue3';
import Icon from '@/Components/AcademyIcon.vue';
defineProps({ lesson: Object });
</script>
<template>
    <article class="lesson-card">
        <div class="lesson-card-top"><span class="module-icon"><Icon :name="lesson.module?.icon || 'code'" /></span><button class="icon-button" :aria-label="lesson.bookmarked ? 'Remove bookmark' : 'Bookmark lesson'" :aria-pressed="!!lesson.bookmarked" @click="router.put(route('lessons.bookmark', lesson.slug), { bookmarked: !lesson.bookmarked }, { preserveScroll: true })"><Icon name="bookmark" :class="{ 'text-accent': lesson.bookmarked }" size="18" /></button></div>
        <span class="eyebrow">{{ lesson.module?.title }}</span>
        <Link :href="route('lessons.show', lesson.slug)" class="lesson-card-title">{{ lesson.title }}</Link>
        <p>{{ lesson.summary }}</p>
        <div class="lesson-card-bottom"><span class="level-tag" :class="lesson.difficulty">{{ lesson.difficulty }}</span><span><Icon name="clock" size="14" /> {{ lesson.estimated_minutes }} min</span><span v-if="lesson.completed" class="completed-label"><Icon name="check" size="15" /> Done</span><Link v-else :href="route('lessons.show', lesson.slug)" class="card-arrow" aria-label="Open lesson"><Icon name="arrow" size="18" /></Link></div>
    </article>
</template>
