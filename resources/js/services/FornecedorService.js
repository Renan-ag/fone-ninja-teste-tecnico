import api from "@/api/axios";

export default {
    async listar() {
        return api.get("/fornecedores");
    },
    async visualizar(id) {
        return api.get(`/fornecedores/${id}`);
    },
    async cadastrar(fornecedor) {
        return api.post("/fornecedores", fornecedor);
    },
    async atualizar(id, fornecedor) {
        return api.put(`/fornecedores/${id}`, fornecedor);
    },
    async excluir(id) {
        return api.delete(`/fornecedores/${id}`);
    },
};
