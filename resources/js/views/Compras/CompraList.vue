<template>
    <v-container>
        <div class="mb-4 flex justify-between gap-2 flex-col md:flex-row">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Compras</h1>
                <p class="text-gray-600">
                    Gerencie suas compras de forma eficiente.
                </p>
            </div>

            <v-btn
                link
                to="/compras/novo"
                color="primary"
                @click="abrirDialogCadastro"
            >
                + Adicionar Compra
            </v-btn>
        </div>

        <v-data-table
            :headers="headers"
            :items="compras"
            :page.sync="page"
            :loading="loading"
            v-model:page="page"
            loading-text="Carregando dados..."
            no-data-text="Nenhuma compra encontrada."
            class="elevation-1"
        >
            <template v-slot:item.status="{ item }">
                {{
                    String(item.status)
                        .toLowerCase()
                        .split(" ")
                        .map(
                            (palavra) =>
                                palavra.charAt(0).toUpperCase() +
                                palavra.slice(1)
                        )
                        .join(" ")
                }}
            </template>
            <template v-slot:item.total="{ item }">
                {{ parsePreco(item.total) }}
            </template>
            <template v-slot:item.lucro="{ item }">
                {{ parsePreco(item.lucro) }}
            </template>
            <template v-slot:item.created_at="{ item }">
                {{
                    new Date(item.created_at).toLocaleDateString("pt-BR", {
                        day: "2-digit",
                        month: "2-digit",
                        year: "numeric",
                    })
                }}
            </template>
            <template v-slot:item.actions="{ item }">
                <v-icon small class="mr-2" @click="visualizar(item)">
                    mdi-eye
                </v-icon>
                <v-icon small class="mr-2" @click="editar(item)">
                    mdi-pencil
                </v-icon>
                <v-icon small @click="confirmarDeletar(item)">
                    mdi-delete
                </v-icon>
            </template>
            <template v-slot:bottom>
                <div class="text-center pt-2">
                    <v-pagination
                        v-model="page"
                        :length="totalPages"
                    ></v-pagination>
                </div>
            </template>
        </v-data-table>

        <!-- Dialog para visualização -->
        <v-dialog v-model="dialogVisualizar" max-width="500px">
            <v-card>
                <v-card-title>Detalhes da Venda</v-card-title>
                <v-card-text class="flex flex-col gap-3">
                    <p><strong>ID:</strong> {{ selectedItem.id }}</p>
                    <p><strong>Fornecedor:</strong> {{ selectedItem.fornecedor.nome }}</p>
                    <p>
                        <strong>Total da Venda:</strong>
                        {{ parsePreco(selectedItem.total) }}
                    </p>                                        
                    <p>
                        <strong>Data de Criação:</strong>
                        {{
                            new Date(
                                selectedItem.created_at
                            ).toLocaleDateString()
                        }}
                    </p>
                    <p>
                        <strong>Data de Atualização:</strong>
                        {{
                            new Date(
                                selectedItem.updated_at
                            ).toLocaleDateString()
                        }}
                    </p>
                    <v-data-table
                        :headers="[
                            { title: 'Nome', value: 'produto.nome' },
                            { title: 'Quantidade', value: 'quantidade' },
                            { title: 'Preço', value: 'preco_unitario' },
                        ]"
                        :items="selectedItem.compra_produtos"
                        hide-default-footer
                    >
                        <template v-slot:item.preco_unitario="{ item }">
                            {{ parsePreco(item.preco_unitario) }}
                        </template>
                    </v-data-table>
                </v-card-text>
                <v-card-actions>
                    <v-btn
                        color="primary"
                        text
                        @click="dialogVisualizar = false"
                        >Fechar</v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Dialog para confirmação de deleção -->
        <v-dialog v-model="dialogDeletar" max-width="500px">
            <v-card>
                <v-card-title>Confirmação</v-card-title>
                <v-card-text>
                    Tem certeza que deseja deletar a compra "{{
                        selectedItem.id
                    }}"?
                </v-card-text>
                <v-card-actions>
                    <v-btn color="primary" text @click="dialogDeletar = false"
                        >Cancelar</v-btn
                    >
                    <v-spacer></v-spacer>
                    <v-btn color="error" text @click="deletarItem"
                        >Deletar</v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
import CompraService from "@/services/CompraService";
import { parsePreco } from "../../utils/parsers";

export default {
    data() {
        return {
            page: 1,
            totalPages: 1,
            loading: false,
            dialogVisualizar: false,
            dialogDeletar: false,
            selectedItem: {},
            headers: [
                { title: "ID", value: "id" },
                { title: "Fornecedor", value: "fornecedor.nome" },
                { title: "Total", value: "total" },
                { title: "Data", value: "created_at" },
                { title: "Ações", value: "actions", sortable: false },
            ],
            compras: [],
            parsePreco,
        };
    },
    async created() {
        await this.carregarCompras();
    },
    methods: {
        async carregarCompras() {
            try {
                this.loading = true;
                const response = await CompraService.listar();

                this.compras = response.data.data.data;
                this.page = response.data.data.current_page;
                this.totalPages = response.data.data.last_page;
            } catch (error) {
                console.error("Erro ao carregar compras:", error);
                alert("Erro ao carregar compras");
            } finally {
                this.loading = false;
            }
        },
        visualizar(item) {
            this.selectedItem = { ...item };
            this.dialogVisualizar = true;
        },
        confirmarDeletar(item) {
            this.selectedItem = { ...item };
            this.dialogDeletar = true;
        },
        async deletarItem() {
            try {
                await CompraService.excluir(this.selectedItem.id);
                this.compras = this.compras.filter(
                    (c) => c.id !== this.selectedItem.id
                );
                this.dialogDeletar = false;
            } catch (error) {
                console.error("Erro ao deletar compra:", error);
                alert("Erro ao deletar compra");
            }
        },
        editar(item) {
            this.$router.push(`/compras/editar/${item.id}`);
        },
        abrirDialogCadastro(modo, compra = null) {
            this.modoAtual = modo;
            this.selectedItem = compra || null; 
            this.dialog = true;
        },
        fecharDialog() {
            this.dialog = false;
            this.modoAtual = "create";
            this.selectedItem = null;
        },
    },
};
</script>
