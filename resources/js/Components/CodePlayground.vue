<script setup>
import { computed, ref, watch } from 'vue';
import hljs from 'highlight.js/lib/core';
import php from 'highlight.js/lib/languages/php';
import sql from 'highlight.js/lib/languages/sql';
import bash from 'highlight.js/lib/languages/bash';
import javascript from 'highlight.js/lib/languages/javascript';
import xml from 'highlight.js/lib/languages/xml';
import css from 'highlight.js/lib/languages/css';
import Icon from '@/Components/AcademyIcon.vue';
hljs.registerLanguage('php', php); hljs.registerLanguage('sql', sql); hljs.registerLanguage('bash', bash); hljs.registerLanguage('javascript', javascript); hljs.registerLanguage('xml', xml); hljs.registerLanguage('css', css);
const props = defineProps({ example: Object });
const source = ref(props.example.source_code), editing = ref(false), copied = ref(false), output = ref(false), error = ref('');
watch(() => props.example.id, () => { source.value = props.example.source_code; editing.value = false; output.value = false; });
const highlighted = computed(() => hljs.highlight(source.value, { language: props.example.language === 'blade' ? 'xml' : props.example.language, ignoreIllegals: true }).value);
async function copy() { try { await navigator.clipboard.writeText(source.value); copied.value = true; setTimeout(() => copied.value = false, 2000); } catch { error.value = 'Clipboard unavailable. Use Edit, then select and copy the code.'; } }
</script>
<template>
    <section class="code-example">
        <div class="code-toolbar"><span><span class="code-dot" />{{ example.title }}</span><span class="code-actions"><small>{{ example.language.toUpperCase() }}</small><button @click="editing = !editing">{{ editing ? 'Highlight' : 'Edit' }}</button><button @click="source = example.source_code; output = false; error = ''">Reset</button><button @click="copy"><Icon :name="copied ? 'check' : 'copy'" size="14" />{{ copied ? 'Copied' : 'Copy' }}</button></span></div>
        <textarea v-if="editing" v-model="source" class="code-editor" :aria-label="`Edit ${example.title}`" spellcheck="false" rows="12" />
        <pre v-else tabindex="0"><code v-html="highlighted" /></pre>
        <div class="code-output-bar"><span>Editable locally · no server execution</span><button v-if="example.expected_output !== null" @click="output = !output">{{ output ? 'Hide' : 'View' }} documented output <span>↗</span></button></div>
        <div v-if="output" class="documented-output"><strong>Documented output for the original example</strong><p v-if="source !== example.source_code">You edited this code. The output below has not been recalculated.</p><pre>{{ example.expected_output || '(No console output)' }}</pre></div>
        <p v-if="error" role="status" class="error-text">{{ error }}</p>
        <p class="code-explanation">{{ example.explanation }}</p>
    </section>
</template>
