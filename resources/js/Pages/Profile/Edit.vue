<script setup>
import AcademyLayout from '@/Layouts/AcademyLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
defineProps({ mustVerifyEmail: Boolean, status: String });
</script>
<template><Head title="Settings & account" /><AcademyLayout title="Settings & account"><div class="page-heading"><div><span class="eyebrow">MAKE YOURSELF AT HOME</span><h1>Your account, your workspace.</h1><p>Update your details and keep your account secure.</p></div></div><div class="profile-panels"><section class="panel"><UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" /></section><section class="panel"><UpdatePasswordForm /></section><section class="panel"><DeleteUserForm /></section></div></AcademyLayout></template>
