<template>
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800">
            Bem-vindo ao ERP de Estoque
        </h2>
        <p class="text-gray-600">
            Gerencie suas vendas, produtos e estoque de forma eficiente.
        </p>
    </div>

    <!-- Cards de Métricas -->
    <v-row>
        <v-col cols="12" md="4">
            <v-card elevation="2" class="p-6 !rounded-xl">
                <v-card-title class="!text-xl !font-semibold text-gray-800">
                    Total de Vendas
                </v-card-title>
                <v-card-text class="!text-2xl !font-bold">
                    {{ metrics.totalVendas }}
                </v-card-text>
            </v-card>
        </v-col>
        <v-col cols="12" md="4">
            <v-card elevation="2" class="p-6 !rounded-xl">
                <v-card-title class="!text-xl !font-semibold text-gray-800">
                    Estoque Baixo
                </v-card-title>
                <v-card-text class="!text-2xl !font-bold">
                    {{ metrics.estoqueBaixo }}
                </v-card-text>
            </v-card>
        </v-col>
        <v-col cols="12" md="4">
            <v-card elevation="2" class="p-6 !rounded-xl">
                <v-card-title class="!text-xl !font-semibold text-gray-800">
                    Produtos Cadastrados
                </v-card-title>
                <v-card-text class="!text-2xl !font-bold">
                    {{ metrics.produtosCadastrados }}
                </v-card-text>
            </v-card>
        </v-col>
    </v-row>
</template>

<script setup>
import { ref } from "vue";

// Dados reativos
const metrics = ref({
    totalVendas: 0,
    estoqueBaixo: 0,
    produtosCadastrados: 0,
});
</script>
