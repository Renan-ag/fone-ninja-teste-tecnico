import { createRouter, createWebHistory } from "vue-router";
import Home from "../views/Home.vue";
import CategoriaList from "../views/Categoria/CategoriaList.vue";
import CategoriaEdit from "../views/Categoria/CategoriaEdit.vue";
import ProdutoList from "../views/Produtos/ProdutoList.vue";
import ProdutoEdit from "../views/Produtos/ProdutoEdit.vue";
import FornecedoresList from "../views/Fornecedores/FornecedoresList.vue";
import FornecedoresEdit from "../views/Fornecedores/FornecedoresEdit.vue";
import VendaList from "../views/Vendas/VendaList.vue";
import VendaEdit from "../views/Vendas/VendaEdit.vue";

const routes = [
    { path: "/", component: Home },
    { path: "/categorias", component: CategoriaList },
    { path: "/categorias/novo", component: CategoriaEdit },
    { path: "/categorias/editar/:id", component: CategoriaEdit },
    { path: "/produtos", component: ProdutoList },
    { path: "/produtos/novo", component: ProdutoEdit },
    { path: "/produtos/editar/:id", component: ProdutoEdit },
    { path: "/fornecedores", component: FornecedoresList },
    { path: "/fornecedores/novo", component: FornecedoresEdit },
    { path: "/fornecedores/editar/:id", component: FornecedoresEdit },
    { path: "/vendas", component: VendaList },
    { path: "/vendas/novo", component: VendaEdit },
    { path: "/vendas/editar/:id", component: VendaEdit },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
