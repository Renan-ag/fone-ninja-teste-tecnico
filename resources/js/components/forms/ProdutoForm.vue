<template>
    <div>
        <h2 class="mb-4 text-2xl font-bold">
            {{ modo === "create" ? "Criar Produto" : "Editar Produto" }}
        </h2>

        <div
            v-if="api_error"
            class="text-red-800 border border-red-500 bg-red-100 !py-2 px-4 my-2 rounded-lg"
        >
            {{ api_error }}
        </div>

        <v-form
            ref="form"
            v-model="valid"
            lazy-validation
            class="form-container grid grid-cols-2 gap-2"
        >
            <v-text-field
                v-model="form.nome"
                label="Nome do Produto"
                :rules="[
                    (v) => !!v || 'Nome é obrigatório',
                    (v) =>
                        v.length <= 150 ||
                        'O nome deve ter no máximo 150 caracteres',
                ]"
                required
                outlined
            />

            <v-autocomplete
                v-model="form.categoria_id"
                :items="categorias"
                item-text="nome"
                item-value="id"
                label="Categoria"
                :rules="[(v) => !!v || 'Categoria é obrigatória']"
                required
                outlined
                :loading="loadingCategorias"
                :search-input.sync="searchCategoria"
                clearable
                placeholder="Digite para buscar uma categoria"
                no-data-text="Nenhuma categoria encontrada"
                prepend-inner-icon="mdi-magnify"
                @update:search-input="debouncedSearch"
                @update:search="debouncedSearch"
            />
            <v-text-field
                v-model="form.preco_venda"
                type="number"
                label="Preço"
                prefix="R$"
                :rules="[
                    (v) => !!v || 'Preço é obrigatório',
                    (v) => v > 0 || 'Preço deve ser maior que zero',
                ]"
                required
                outlined
            />
            <v-text-field
                v-model="form.custo_medio"
                type="number"
                label="Custo Médio"
                :disabled="modo === 'edit'"
                prefix="R$"
                :rules="[
                    (v) => !!v || 'Custo Médio é obrigatório',
                    (v) => v > 0 || 'Custo Médio deve ser maior que zero',
                ]"
                required
                outlined
            />
            <v-text-field
                v-model="form.estoque"
                label="Estoque"
                type="number"
                :disabled="modo === 'edit'"
                :rules="[
                    (v) => !!v || 'Estoque é obrigatório',
                    (v) => v >= 0 || 'Estoque deve ser maior ou igual a zero',
                ]"
                required
                outlined
            />
            <v-textarea
                v-model="form.descricao"
                class="col-span-2"
                label="Descrição"
                required
                outlined
            />
            <v-switch
                v-model="form.ativo"
                :true-value="1"
                :false-value="0"
                :label="`Status: ${form.ativo == 1 ? 'Ativo' : 'Inativo'}`"
            ></v-switch>
        </v-form>

        <div class="flex flex-col md:flex-row gap-2 justify-end">
            <v-btn color="grey" text @click="$emit('cancelar')">Cancelar</v-btn>
            <v-btn color="primary" :disabled="!valid || loading" @click="salvar"
                >Salvar</v-btn
            >
        </div>
    </div>
</template>

<script>
import { parsePreco } from "@/utils/parsers";

import CategoriaService from "@/services/CategoriaService";
import ProdutoService from "@/services/ProdutoService";
import debounce from "lodash/debounce";
import { handleApiFormErrors } from "../../utils/handleApiErrors";

export default {
    props: {
        modo: {
            type: String,
            default: "create",
            validator: (value) => ["create", "edit"].includes(value),
        },
        produto: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            valid: true,
            loading: false,
            loadingCategorias: false,
            searchCategoria: null,
            categorias: [],
            api_error: null,
            form: {
                id: null,
                nome: "",
                preco_venda: null,
                descricao: "",
                categoria_id: null,
                estoque: null,
                ativo: 1,
            },
            categoriaSelecionada: null,
            parsePreco,
        };
    },
    watch: {
        produto: {
            handler(newProduto) {
                if (this.modo === "edit" && newProduto && newProduto.id) {
                    this.form = {
                        id: newProduto.id,
                        nome: newProduto.nome || "",
                        preco_venda:
                            Number(newProduto.preco_venda).toFixed(2) || null,
                        custo_medio:
                            Number(newProduto.custo_medio).toFixed(2) || null,
                        estoque: Number(newProduto.estoque) || null,
                        descricao: newProduto.descricao || "",
                        categoria_id: Number(newProduto.categoria_id) || null,
                        ativo: Number(newProduto.ativo) == 1 ? 1 : 0,
                    };

                    if (this.produto.categoria_id) {
                        this.carregarCategoriaPorId(this.produto.categoria_id);
                    }
                }
            },
            immediate: true,
            deep: true,
        },
    },
    created() {
        this.carregarCategorias();
    },
    methods: {
        debouncedSearch: debounce(function (search) {
            this.carregarCategorias(search);
        }, 300),
        async carregarCategorias(search = null) {
            this.loadingCategorias = true;
            try {
                const response = await CategoriaService.listar({
                    pesquisa: search,
                    apenasAtivos: true,
                });

                const apiData = response.data.data.data;
                this.categorias = apiData.map((categoria) => ({
                    id: categoria.id,
                    title: categoria.nome,
                }));

                if (this.modo === "edit" && this.categoriaSelecionada) {
                    const exists = this.categorias.some(
                        (cat) => cat.id === this.categoriaSelecionada.id
                    );
                    if (!exists) {
                        this.categorias.push(this.categoriaSelecionada);
                    }
                }
            } catch (error) {
                console.error("Erro ao carregar categorias:", error);
                alert("Erro ao carregar categorias");
                this.$emit("erro", error);
            } finally {
                this.loadingCategorias = false;
            }
        },
        async carregarCategoriaPorId(id) {
            this.loadingCategorias = true;

            try {
                const response = await CategoriaService.visualizar(id);
                this.categoriaSelecionada = {
                    id: response.data.data.id,
                    title: response.data.data.nome,
                };
                this.categorias = [this.categoriaSelecionada]; // Inicializa com a categoria do produto
            } catch (error) {
                console.error("Erro ao carregar categoria:", error);
                alert("Erro ao carregar categoria");
                this.$emit("erro", error);
            } finally {
                this.loadingCategorias = false;
            }
        },
        async salvar() {
            this.api_error = null;
            if (this.$refs.form.validate()) {
                this.loading = true;
                try {
                    const payload = {
                        ...this.form,
                    };
                    if (this.modo === "create") {
                        await ProdutoService.cadastrar(payload);
                        this.$emit("salvo");
                    } else {
                        await ProdutoService.atualizar(this.form.id, payload);
                        this.$emit("salvo");
                    }
                } catch (error) {
                    console.error(
                        `Erro ao ${
                            this.modo === "create" ? "criar" : "atualizar"
                        } produto:`,
                        error
                    );
                    const errorKeys = Object.keys(error.response.data.errors);
                    this.api_error = handleApiFormErrors(errorKeys[0]);
                    this.$emit("erro", error);
                } finally {
                    this.loading = false;
                }
            }
        },
    },
};
</script>
