import { createRouter, createWebHistory } from "vue-router";
import Home from "../views/Home.vue";
import CategoriaList from "../views/Categoria/CategoriaList.vue";
import CategoriaEdit from "../views/Categoria/CategoriaEdit.vue";

const routes = [
    { path: "/", component: Home },
    { path: "/categorias", component: CategoriaList },
    { path: "/categorias/novo", component: CategoriaEdit },
    { path: "/categorias/editar/:id", component: CategoriaEdit },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
