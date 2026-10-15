<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Icon from '@/Components/AcademyIcon.vue';
const props = defineProps({ quiz: Object, slug: String });
const answers = ref({}), result = ref(null), busy = ref(false), error = ref('');
async function submit() { busy.value = true; error.value = ''; try { const response = await axios.post(route('lessons.quiz', props.slug), { answers: answers.value }); result.value = response.data; } catch (e) { error.value = e.response?.data?.errors?.answers?.[0] || e.response?.data?.message || 'Could not submit. Please try again.'; } finally { busy.value = false; } }
function feedback(question) { return result.value?.feedback.find(item => item.question_id === question.id); }
</script>
<template>
    <section id="knowledge-check" class="quiz-panel panel">
        <div class="section-heading"><div><span class="eyebrow text-accent">PUT IT INTO PRACTICE</span><h2>{{ quiz.title }}</h2></div><span class="module-icon"><Icon name="trophy" /></span></div>
        <p class="muted">{{ quiz.questions.length }} questions · {{ quiz.difficulty }} · retake anytime</p>
        <form @submit.prevent="submit">
            <fieldset v-for="(question, index) in quiz.questions" :key="question.id" class="quiz-question" :disabled="!!result"><legend><span>{{ String(index + 1).padStart(2, '0') }}</span>{{ question.prompt }}</legend>
                <label v-for="option in question.options" :key="option.id" :class="['quiz-option', { chosen: answers[question.id] === option.id, correct: feedback(question)?.correct_option_id === option.id, incorrect: result && answers[question.id] === option.id && feedback(question)?.correct_option_id !== option.id }]"><input v-model="answers[question.id]" type="radio" :name="`question-${question.id}`" :value="option.id" required />{{ option.label }}</label>
                <p v-if="result" class="quiz-explanation">{{ feedback(question)?.explanation }}</p>
            </fieldset>
            <p v-if="error" role="alert" class="error-text">{{ error }}</p>
            <div v-if="result" class="quiz-result" role="status"><strong>{{ result.attempt.score }}%</strong><span>{{ result.attempt.correct_answers }} of {{ result.attempt.total_questions }} correct. Your score is saved.</span><button type="button" class="button secondary" @click="result = null; answers = {}">Try again</button></div>
            <button v-else class="button primary" :disabled="busy">{{ busy ? 'Checking answers…' : 'Check my understanding' }}<Icon name="arrow" size="17" /></button>
        </form>
    </section>
</template>
