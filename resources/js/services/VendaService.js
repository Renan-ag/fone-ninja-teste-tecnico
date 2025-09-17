import api from "@/api/axios";

export default {
    async listar() {
        return api.get("/vendas");
    },
    async visualizar(id) {
        return api.get(`/vendas/${id}`);
    },
    async cadastrar(venda) {
        return api.post("/vendas", venda);
    },
    async atualizar(id, venda) {
        return api.put(`/vendas/${id}`, venda);
    },
    async excluir(id) {
        return api.delete(`/vendas/${id}`);
    },
};
