<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AcademyLayout from '@/Layouts/AcademyLayout.vue';
import CodePlayground from '@/Components/CodePlayground.vue';
import LessonQuiz from '@/Components/LessonQuiz.vue';
import Icon from '@/Components/AcademyIcon.vue';
const props = defineProps({ lesson: Object, preview: Boolean, progress: Object, bookmarked: Boolean, siblings: Array, previous: Object, next: Object });
const busy = ref(false);
function complete() { busy.value = true; router.put(route('lessons.progress', props.lesson.slug), { completed: !props.progress?.completed_at }, { preserveScroll: true, onFinish: () => busy.value = false }); }
</script>
<template>
    <Head :title="lesson.title" />
    <AcademyLayout :title="lesson.module.title">
        <div v-if="preview" class="preview-banner">ADMIN PREVIEW · This lesson or its module is unpublished. Progress and quiz submissions are disabled.</div>
        <nav class="lesson-breadcrumb" aria-label="Breadcrumb"><Link :href="route('lessons.index')">Library</Link><Icon name="chevron" size="12" /><Link :href="route('lessons.index', { module: lesson.module_id })">{{ lesson.module.title }}</Link></nav>
        <div class="reader-grid">
            <article class="lesson-reader">
                <div class="lesson-meta"><span class="level-tag" :class="lesson.difficulty">{{ lesson.difficulty }}</span><span><Icon name="clock" size="15" />{{ lesson.estimated_minutes }} min read</span><span>PHP 8.2 · Laravel 12</span></div>
                <h1>{{ lesson.title }}</h1><p class="lesson-intro">{{ lesson.summary }}</p>
                <div class="lesson-action-row"><button v-if="!preview" class="button secondary" @click="router.put(route('lessons.bookmark', lesson.slug), { bookmarked: !bookmarked }, { preserveScroll: true })"><Icon name="bookmark" size="16" />{{ bookmarked ? 'Bookmarked' : 'Save lesson' }}</button><Link v-if="$page.props.auth.user.is_admin" :href="route('admin.lessons.edit', lesson.slug)" class="text-link">Edit lesson ↗</Link><span v-for="tag in lesson.tags" :key="tag.id" class="tag">#{{ tag.name }}</span></div>
                <section id="objectives" class="objectives-box"><span class="eyebrow">BY THE END OF THIS LESSON</span><ul><li v-for="objective in lesson.objectives" :key="objective"><Icon name="check" size="16" />{{ objective }}</li></ul><p v-if="lesson.prerequisites"><strong>Before you start:</strong> {{ lesson.prerequisites }}</p></section>
                <section v-for="section in lesson.sections" :id="`section-${section.id}`" :key="section.id" :class="['lesson-section', `section-${section.kind}`]"><h2>{{ section.title }}</h2><div class="prose-text">{{ section.body }}</div></section>
                <section v-if="lesson.code_examples.length" id="examples" class="lesson-section"><span class="eyebrow text-accent">LEARN BY DOING</span><h2>Explore the code</h2><p class="muted">Read it, change it, make it yours. Output is documented, not executed.</p><CodePlayground v-for="example in lesson.code_examples" :key="example.id" :example="example" /></section>
                <section v-if="lesson.exercises.length" id="exercises" class="lesson-section"><h2>Your turn</h2><div v-for="(exercise, index) in lesson.exercises" :key="exercise.id" class="exercise panel"><span class="eyebrow">EXERCISE {{ String(index + 1).padStart(2, '0') }}</span><h3>{{ exercise.title }}</h3><p class="prose-text">{{ exercise.instructions }}</p><details><summary>Reveal a suggested approach</summary><div class="prose-text solution">{{ exercise.solution }}</div></details></div></section>
                <section v-if="lesson.project_guide" class="project-guide panel"><span class="eyebrow text-accent">GUIDED PROJECT</span><h2>Your project blueprint</h2><p class="prose-text">{{ lesson.project_guide.brief }}</p><h3>Build in milestones</h3><ol><li v-for="step in lesson.project_guide.milestones" :key="step">{{ step }}</li></ol><h3>Acceptance criteria</h3><ul><li v-for="criterion in lesson.project_guide.acceptance_criteria" :key="criterion">{{ criterion }}</li></ul></section>
                <LessonQuiz v-if="lesson.quiz && !preview" :key="lesson.quiz.id" :quiz="lesson.quiz" :slug="lesson.slug" />
                <div v-if="!preview" class="completion-box"><div><h3>{{ progress?.completed_at ? 'Another concept in your toolkit.' : 'Ready for the next step?' }}</h3><p>{{ progress?.completed_at ? 'You completed this lesson. Revisit it anytime.' : 'Mark this lesson complete to update your learning progress.' }}</p></div><button class="button primary" :disabled="busy" @click="complete"><Icon name="check" size="17" />{{ progress?.completed_at ? 'Mark incomplete' : 'Complete lesson' }}</button></div>
                <nav class="lesson-navigation" aria-label="Previous and next lesson"><Link v-if="previous" :href="route('lessons.show', previous.slug)"><small>← PREVIOUS LESSON</small><strong>{{ previous.title }}</strong></Link><span v-else /><Link v-if="next" :href="route('lessons.show', next.slug)" class="next-lesson"><small>NEXT LESSON →</small><strong>{{ next.title }}</strong></Link></nav>
            </article>
            <aside class="lesson-toc"><div class="toc-sticky"><span class="eyebrow">ON THIS PAGE</span><nav aria-label="Table of contents"><a href="#objectives">Learning objectives</a><a v-for="section in lesson.sections" :key="section.id" :href="`#section-${section.id}`">{{ section.title }}</a><a v-if="lesson.code_examples.length" href="#examples">Explore the code</a><a v-if="lesson.exercises.length" href="#exercises">Your turn</a><a v-if="lesson.quiz && !preview" href="#knowledge-check">Knowledge check</a></nav><div class="toc-course"><span class="eyebrow">IN THIS MODULE</span><Link v-for="(sibling, index) in siblings" :key="sibling.id" :href="route('lessons.show', sibling.slug)" :class="{ active: sibling.id === lesson.id }"><small>{{ index + 1 }}</small>{{ sibling.title }}</Link></div></div></aside>
        </div>
    </AcademyLayout>
</template>
