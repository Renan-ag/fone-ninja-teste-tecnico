import api from "@/api/axios";

export default {
    async listar({apenasAtivos = false, pesquisa = null} = {}) {
        return api.get("/categorias", {
            params: {
                ativo: apenasAtivos ? 1 : 0,
                pesquisa,
            },
        });
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
