<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';

const page = usePage();
const isNotLocalhost = window.location.hostname !== 'localhost';
</script>

<template>
    <div
        v-if="
            page.props.auth.user.roles?.find((r) => r.role === 'admin') &&
            isNotLocalhost
        "
        class="fixed top-5 right-5"
    >
        <Link href="/feature">
            <Button variant="secondary">Features</Button>
        </Link>
    </div>
</template>
