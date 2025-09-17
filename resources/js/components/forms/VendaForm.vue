<template>
    <div>
        <h2 class="mb-4 text-2xl font-bold">
            {{ modo === "create" ? "Criar Venda" : "Editar Venda" }}
        </h2>

        <v-form
            ref="form"
            v-model="valid"
            lazy-validation
            class="form-container flex flex-col md:grid md:grid-cols-2 gap-2"
        >
            <v-text-field
                v-model="form.cliente"
                label="Nome do Cliente"
                :rules="[(v) => !!v || 'Nome do cliente é obrigatório']"
                required
                outlined
                class="md:col-span-2"
                :error-messages="api_error?.cliente"
            />

            <v-select
                v-model="form.status"
                :items="statusOptions"
                item-title="text"
                item-value="value"
                label="Status"
                :rules="[(v) => !!v || 'Status é obrigatório']"
                required
                outlined
                :error-messages="api_error?.status"
            />

            <v-text-field
                v-model="form.data_venda"
                label="Data da Venda"
                type="date"
                required
                outlined
                :error-messages="api_error?.data_venda"
            />

            <v-data-table
                :headers="produtoHeaders"
                :items="produtosSelecionados"
                item-key="produto_id"
                class="col-span-2 max-h-80"
                :loading="loadingProdutos"
                :items-per-page="50"
                :fixed-header="true"
                hide-default-footer
            >
                <template v-slot:item.quantidade="{ item, index }">
                    <v-text-field
                        v-model.number="item.quantidade"
                        type="number"
                        min="1"
                        class="pt-4"
                        :rules="[
                            (v) => !!v || 'Quantidade é obrigatória',
                            (v) =>
                                v > 0 || 'Quantidade deve ser maior que zero',
                        ]"
                        dense
                        outlined
                        @blur="blurProdutoQuantidadeInput(index)"
                        :error-messages="
                            api_error &&
                            Object.keys(api_error).includes(
                                `produtos.${index}.quantidade`
                            )
                                ? api_error[`produtos.${index}.quantidade`]
                                : []
                        "
                        @input="calcularTotal"
                    />
                </template>
                <template v-slot:item.subtotal="{ item }">
                    {{ parsePreco(item.preco_unitario * item.quantidade) }}
                </template>
                <template v-slot:item.preco_unitario="{ item }">
                    {{ parsePreco(item.preco_unitario) }}
                </template>
                <template v-slot:item.acoes="{ item }">
                    <v-btn
                        icon
                        small
                        color="error"
                        @click="removerProduto(item)"
                    >
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title>Produtos</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-autocomplete
                            ref="autocompleteProduto"
                            v-model="produtoSelecionado"
                            :items="produtos"
                            item-title="nome"
                            item-value="id"
                            label="Adicionar Produto"
                            outlined
                            :loading="loadingProdutos"
                            :search-input.sync="searchProduto"
                            placeholder="Digite para buscar um produto"
                            no-data-text="Nenhum produto encontrado"
                            prepend-inner-icon="mdi-magnify"
                            @update:search-input="debouncedSearchProduto"
                            @update:model-value="adicionarProduto"
                            :error-messages="api_error?.produtos"
                            class="mr-4 pt-5 max-w-80"
                        />
                    </v-toolbar>
                </template>
                <template v-slot:no-data>
                    <div class="text-center py-4">
                        Nenhum produto adicionado. Adicione produtos para
                        prosseguir.
                    </div>
                </template>
            </v-data-table>

            <v-text-field
                v-model="form.total"
                label="Total"
                type="number"
                prefix="R$"
                readonly
                outlined
                :error-messages="api_error?.total"
            />

            <v-text-field
                v-model="form.lucro"
                label="Lucro"
                type="number"
                prefix="R$"
                readonly
                outlined
            />
        </v-form>

        <div class="flex flex-col md:flex-row gap-2 justify-end">
            <v-btn color="grey" text @click="$emit('cancelar')">Cancelar</v-btn>
            <v-btn
                color="primary"
                :disabled="loading || !produtosSelecionados.length || !valid"
                @click="salvar"
                >Salvar</v-btn
            >
        </div>
    </div>
</template>

<script>
import ProdutoService from "@/services/ProdutoService";
import VendaService from "@/services/VendaService";
import debounce from "lodash/debounce";
import { parsePreco } from "../../utils/parsers";

export default {
    props: {
        modo: {
            type: String,
            default: "create",
            validator: (value) => ["create", "edit"].includes(value),
        },
        venda: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            valid: true,
            loading: false,
            loadingProdutos: false,
            searchProduto: null,
            produtos: [],
            produtosSelecionados: [],
            produtoSelecionado: null,
            api_error: {},
            statusOptions: [
                { text: "Pendente", value: "pendente" },
                { text: "Concluída", value: "concluída" },
            ],
            produtoHeaders: [
                { title: "Nome", value: "nome", sortable: false },
                {
                    title: "Preço Unitário (R$)",
                    value: "preco_unitario",
                    sortable: false,
                },
                { title: "Quantidade", value: "quantidade", sortable: false },
                { title: "Subtotal (R$)", value: "subtotal", sortable: false },
                { title: "Ações", value: "acoes", sortable: false },
            ],
            form: {
                cliente: "",
                status: "concluída",
                data_venda: "",
                produtos: [],
                total: 0,
                lucro: 0,
            },
            parsePreco,
        };
    },
    watch: {
        venda: {
            handler(newVenda) {
                if (this.modo === "edit" && newVenda && newVenda.id) {
                    this.form = {
                        cliente: newVenda.cliente || "",
                        status: newVenda.status || "concluída",
                        data_venda: newVenda.data_venda || "",
                        total: Number(newVenda.total) || 0,
                        lucro: 0, // Será recalculado
                    };

                    this.form.produtos = (newVenda.venda_produtos || []).map(
                        (p) => ({
                            produto_id: p.produto_id,
                            quantidade: Number(p.quantidade),
                            preco_unitario: Number(p.preco_unitario),
                        })
                    );
                    this.produtosSelecionados = (
                        newVenda.venda_produtos || []
                    ).map((p) => ({
                        produto_id: p.produto_id,
                        nome: p.produto.nome || "",
                        preco_unitario: Number(p.preco_unitario),
                        custo_medio: Number(p.produto.custo_medio) || 0,
                        quantidade: Number(p.quantidade),
                        subtotal:
                            Number(p.preco_unitario) * Number(p.quantidade),
                    }));
                    this.calcularTotal(); // Recalcula total e lucro
                } else {
                    this.form = {
                        cliente: "",
                        status: "concluída",
                        produtos: [],
                        data_venda: "",
                        total: 0,
                        lucro: 0,
                    };
                    this.produtosSelecionados = [];
                }
            },
            immediate: true,
            deep: true,
        },
    },
    created() {
        this.carregarProdutos();
    },
    methods: {
        debouncedSearchProduto: debounce(function (search) {
            this.carregarProdutos(search);
        }, 300),
        async carregarProdutos(search = null) {
            this.loadingProdutos = true;
            try {
                const response = await ProdutoService.listar({
                    pesquisa: search,
                    apenasAtivos: true,
                });
                const selectedProductsId = this.produtosSelecionados.map(
                    (produto) => produto.produto_id
                );

                this.produtos = response.data.data.data
                    .filter(
                        (produto) => !selectedProductsId.includes(produto.id)
                    )
                    .map((produto) => ({
                        id: produto.id,
                        nome: produto.nome,
                        preco_venda: Number(produto.preco_venda).toFixed(2),
                        custo_medio: Number(produto.custo_medio).toFixed(2),
                    }));
            } catch (error) {
                console.error("Erro ao carregar produtos:", error);
                alert("Erro ao carregar produtos");
                this.$emit("erro", error);
            } finally {
                this.loadingProdutos = false;
            }
        },
        adicionarProduto() {
            if (this.produtoSelecionado) {
                const produto = this.produtos.find(
                    (p) => p.id === this.produtoSelecionado
                );

                if (
                    produto &&
                    !this.produtosSelecionados.some(
                        (p) => p.produto_id === produto.id
                    )
                ) {
                    const novoProduto = {
                        produto_id: produto.id,
                        nome: produto.nome,
                        preco_unitario: Number(produto.preco_venda),
                        custo_medio: Number(produto.custo_medio),
                        quantidade: 1,
                        subtotal: Number(produto.preco_venda).toFixed(2),
                    };
                    this.produtosSelecionados.push(novoProduto);
                    this.produtos = this.produtos.filter(
                        (p) => p.id !== produto.id
                    );

                    this.form.produtos.push({
                        produto_id: produto.id,
                        quantidade: 1,
                        preco_unitario: Number(produto.preco_venda),
                    });
                    this.calcularTotal();
                }
                this.produtoSelecionado = null;
                this.searchProduto = null;
            }
        },
        removerProduto(item) {
            const index = this.produtosSelecionados.findIndex(
                (p) => p.produto_id === item.produto_id
            );
            if (index > -1) {
                this.produtosSelecionados.splice(index, 1);
                this.form.produtos.splice(index, 1);
                this.produtos.push({
                    id: item.produto_id,
                    nome: item.nome,
                    preco_venda: item.preco_unitario,
                    custo_medio: item.custo_medio,
                });

                this.calcularTotal();
            }
        },
        calcularTotal() {
            this.produtosSelecionados.forEach((p) => {
                p.subtotal = (
                    Number(p.preco_unitario) * Number(p.quantidade)
                ).toFixed(2);
            });
            const total = this.produtosSelecionados.reduce(
                (sum, p) => sum + Number(p.subtotal),
                0
            );
            const lucro = this.produtosSelecionados.reduce(
                (sum, p) =>
                    sum +
                    (Number(p.preco_unitario) - Number(p.custo_medio)) *
                        Number(p.quantidade),
                0
            );
            this.form.total = total.toFixed(2);
            this.form.lucro = lucro.toFixed(2);
            this.form.produtos = this.produtosSelecionados.map((p) => ({
                produto_id: p.produto_id,
                quantidade: Number(p.quantidade),
                preco_unitario: Number(p.preco_unitario),
            }));
        },
        blurProdutoQuantidadeInput(index) {
            const keyValidation = Object.keys(this.api_error).includes(
                `produtos.${index}.quantidade`
            );
            if (!keyValidation) return;

            if (this.api_error[`produtos.${index}.quantidade`])
                delete this.api_error[`produtos.${index}.quantidade`];
        },
        async salvar() {
            this.api_error = null;
            if (
                this.$refs.form.validate() &&
                this.produtosSelecionados.length > 0
            ) {
                this.loading = true;
                try {
                    const payload = {
                        cliente: this.form.cliente,
                        status: this.form.status,
                        total: Number(this.form.total),
                        produtos: this.form.produtos,
                        data_venda: this.form.data_venda,
                    };

                    if (this.modo === "create") {
                        await VendaService.cadastrar(payload);
                        this.$emit("salvo");
                    } else {
                        await VendaService.atualizar(
                            this.form.id || this.venda.id,
                            payload
                        );
                        this.$emit("salvo");
                    }
                } catch (error) {
                    console.error(
                        `Erro ao ${
                            this.modo === "create" ? "criar" : "atualizar"
                        } venda:`,
                        error
                    );

                    if (error.response?.data?.errors) {
                        this.api_error = error.response.data.errors;
                    }

                    this.$emit("erro", error);
                } finally {
                    this.loading = false;
                }
            }
        },
    },
};
</script>
