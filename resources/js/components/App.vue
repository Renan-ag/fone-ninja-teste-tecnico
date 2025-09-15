<template>
    <v-app>
        <Sidebar />

        <v-main>
            <v-container>
                <router-view />
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
import Sidebar from "./Sidebar.vue";

export default {
    name: "App",
    components: {
        Sidebar,
    },
};
</script>
