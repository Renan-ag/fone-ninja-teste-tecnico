import api from "@/api/axios";

export default {
    async listar({ apenasAtivos = false, pesquisa = null } = {}) {
        return api.get("/produtos", {
            params: {
                ativo: apenasAtivos ? 1 : 0,
                pesquisa,
            },
        });
    },
    async visualizar(id) {
        return api.get(`/produtos/${id}`);
    },
    async cadastrar(produto) {
        return api.post("/produtos", produto);
    },
    async atualizar(id, produto) {
        return api.put(`/produtos/${id}`, produto);
    },
    async excluir(id) {
        return api.delete(`/produtos/${id}`);
    },
};
