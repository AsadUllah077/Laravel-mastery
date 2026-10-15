<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
const page = usePage();
const isRegister = computed(() => page.url.startsWith('/register'));
const isLogin = computed(() => page.url.startsWith('/login'));
</script>
<template><div class="auth-layout"><aside class="auth-story"><Link href="/" class="brand"><span class="brand-mark">L<span>∕</span></span><span><strong>Laravel Mastery<span class="brand-dot">.</span></strong><small>ACADEMY</small></span></Link><div><h1>Understand the why.<br />Master the how.</h1><p>Your path from PHP fundamentals to thoughtful, confident Laravel development.</p><div class="auth-code"><span class="muted">// One lesson at a time.</span><br />$you->learn()<br />&nbsp;&nbsp;&nbsp;&nbsp;->practice()<br />&nbsp;&nbsp;&nbsp;&nbsp;->buildSomethingGreat();</div></div><small>A WORKSPACE FOR YOUR NEXT CHAPTER.</small></aside><main class="auth-content"><div class="auth-content-inner"><header class="auth-heading"><span class="eyebrow">YOUR DEVELOPER JOURNEY</span><h2>{{ isRegister ? 'A great place to start.' : isLogin ? 'Welcome back, builder.' : 'Let’s get you sorted.' }}</h2><p>{{ isRegister ? 'Create an account. Build a deeper understanding.' : isLogin ? 'Sign in and pick up where curiosity left you.' : 'Keep your account secure and your learning moving.' }}</p></header><slot /><p v-if="isLogin" class="auth-register-note">New to the academy? <Link :href="route('register')">Start learning →</Link></p></div></main></div></template>
