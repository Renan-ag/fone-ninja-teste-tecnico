<template>
    <v-app-bar color="#212121" dark app>
        <!-- Botão para abrir/fechar sidebar -->
        <v-app-bar-nav-icon @click="toggleDrawer" />
        <v-toolbar-title>ERP - Estoque</v-toolbar-title>
    </v-app-bar>
    <v-navigation-drawer v-model="drawer" app>
        <v-list>
            <v-list-item link to="/">
                <template #default>
                    <v-icon class="me-2">mdi-home-outline</v-icon>
                    Inicio
                </template>
            </v-list-item>

            <v-list-item link to="/produtos">
                <template #default>
                    <v-icon class="me-2">mdi-cube-outline</v-icon>
                    Produtos
                </template>
            </v-list-item>

            <v-list-item link to="/vendas">
                <template #default>
                    <v-icon class="me-2">mdi-cart-outline</v-icon>
                    Vendas
                </template>
            </v-list-item>

            <v-list-item link to="/fornecedores">
                <template #default>
                    <v-icon class="me-2">mdi-truck-outline</v-icon>
                    Fornecedores
                </template>
            </v-list-item>

            <v-list-item link to="/categorias">
                <template #default>
                    <v-icon class="me-2">mdi-tag-outline</v-icon>
                    Categorias
                </template>
            </v-list-item>

            <v-list-item link to="/compras">
                <template #default>
                    <v-icon class="me-2">mdi-cart-arrow-down</v-icon>
                    Compras
                </template>
            </v-list-item>
        </v-list>
    </v-navigation-drawer>
</template>

<script setup>
import { ref } from "vue";
const drawer = ref(true);
function toggleDrawer() {
    drawer.value = !drawer.value;
}
</script>
