import api from "@/api/axios";

export default {
    async listar({ apenasAtivos = false, pesquisa = null } = {}) {
        return api.get("/compras", {
            params: {
                ativo: apenasAtivos ? 1 : 0,
                pesquisa,
            },
        });
    },
    async visualizar(id) {
        return api.get(`/compras/${id}`);
    },
    async cadastrar(compra) {
        return api.post("/compras", compra);
    },
    async atualizar(id, compra) {
        return api.put(`/compras/${id}`, compra);
    },
    async excluir(id) {
        return api.delete(`/compras/${id}`);
    },
};
