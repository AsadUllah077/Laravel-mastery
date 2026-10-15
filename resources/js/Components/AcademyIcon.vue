<script setup>
defineProps({ name: { type: String, default: 'code' }, size: { type: [Number, String], default: 20 } });
const paths = {
    home: 'm3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z',
    book: 'M4 3h6a2 2 0 0 1 2 2v16a4 4 0 0 0-4-2H3V4a1 1 0 0 1 1-1ZM20 3h-6a2 2 0 0 0-2 2v16a4 4 0 0 1 4-2h5V4a1 1 0 0 0-1-1Z',
    bookmark: 'M6 3h12v18l-6-4-6 4Z', code: 'm8 7-5 5 5 5m8-10 5 5-5 5m-3-14-2 18',
    box: 'm12 3 9 5-9 5-9-5 9-5Zm-9 5v10l9 5 9-5V8M12 13v10',
    layers: 'm12 3 10 5-10 5L2 8l10-5ZM2 12l10 5 10-5M2 16l10 5 10-5',
    route: 'M5 5h9a4 4 0 0 1 0 8h-4a4 4 0 0 0 0 8h9M3 3h4v4H3ZM17 19h4v4h-4Z',
    zap: 'm13 2-9 12h7l-1 8 10-12h-7Z', pattern: 'M3 3h7v7H3ZM14 14h7v7h-7ZM14 3h7v7h-7ZM3 14h7v7H3Z',
    shield: 'm12 2 9 4v6c0 5-9 10-9 10S3 17 3 12V6l9-4Zm-4 9 3 3 5-5',
    check: 'm5 12 4 4L19 6', gauge: 'M4 19a10 10 0 1 1 16 0M12 12l5-5M12 17v1',
    cloud: 'M6 19a5 5 0 0 1-1-10 7 7 0 0 1 13-2 6 6 0 0 1 0 12H6Z',
    rocket: 'M14 4c3-2 7-2 7-2s0 4-2 7l-8 8-4-4 7-9ZM7 13H3l3-6h5m0 10v4l6-3v-5M5 17l-3 5 5-3',
    search: 'M21 21l-6-6M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z',
    arrow: 'M4 12h16m-6-6 6 6-6 6', clock: 'M12 8v5l3 2M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z',
    history: 'M3 4v6h6M3 10a9 9 0 1 1 1 8M12 7v5l4 2',
    settings: 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM4 4l3 1 2-3h6l2 3 3-1 2 5-2 3 2 3-2 5-3-1-2 3H9l-2-3-3 1-2-5 2-3-2-3Z',
    sun: 'M12 3V1m0 22v-2M3 12H1m22 0h-2M4 4l2 2m12 12 2 2M4 20l2-2M18 6l2-2M17 12a5 5 0 1 1-10 0 5 5 0 0 1 10 0Z',
    moon: 'M21 13A9 9 0 0 1 11 3a9 9 0 1 0 10 10Z',
    menu: 'M4 6h16M4 12h16M4 18h16', close: 'm6 6 12 12M6 18 18 6',
    trophy: 'M8 3h8v7a4 4 0 0 1-8 0V3Zm0 2H3v3a5 5 0 0 0 5 5m8-8h5v3a5 5 0 0 1-5 5M12 14v6m-4 1h8',
    target: 'M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0ZM18 12a6 6 0 1 1-12 0 6 6 0 0 1 12 0ZM14 12a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z',
    logout: 'M9 3H3v18h6m5-15 6 6-6 6m-7-6h13', copy: 'M9 9h12v12H9ZM5 15H3V3h12v2',
    chevron: 'm9 5 7 7-7 7', plus: 'M12 4v16M4 12h16',
};
</script>
<template><svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="paths[name] || paths.code" /></svg></template>
