<template>
    <div class="kpi-card" :class="{ 'kpi-card--active': active }">
        <div class="kpi-value">{{ value }}</div>
        <div class="kpi-label">{{ label }}</div>
    </div>
</template>

<script setup>
defineProps({
    value: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    active: {
        type: Boolean,
        default: false
    }
})
</script>

<style scoped>
.kpi-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    flex: 1;
}

.kpi-card--active {
    background-color: #fef08a; /* Gelber Highlight-Ton aus dem Mockup */
    border-color: #fef08a;
}

.kpi-value {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2px;
}

.kpi-label {
    font-size: 13px;
    color: #475569;
}
</style>