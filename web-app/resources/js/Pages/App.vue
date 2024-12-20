<template>
    <div id="app">
        <router-view></router-view>
    </div>
</template>

<script>
import Dashboard from './components/Dashboard.vue';


export default {
    name: 'App',
    components: {
        Dashboard,
    },
};
</script>