import api from "@/api/axios";

export default {
    async listar() {
        return api.get("/categorias");
    },
    async visualizar(id) {
        return api.get(`/categorias/${id}`);
    },
    async cadastrar(categoria) {
        return api.post("/categorias", categoria);
    },
    async atualizar(id, categoria) {
        return api.put(`/categorias/${id}`, categoria);
    },
    async excluir(id) {
        return api.delete(`/categorias/${id}`);
    },
};
