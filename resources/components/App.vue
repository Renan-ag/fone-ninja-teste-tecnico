<template>
    <div>
        <nav>
            <router-link to="/produtos">Produtos</router-link> |
            <router-link to="/compras">Compras</router-link> |
            <router-link to="/vendas">Vendas</router-link>
        </nav>
        <router-view></router-view>
    </div>
</template>

<script>
export default {
    name: "App",
};
</script>
