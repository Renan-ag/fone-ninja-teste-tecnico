<template>
    <v-container>
        <div class="mb-4 flex justify-between gap-2 flex-col md:flex-row">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Fornecedores</h1>
                <p class="text-gray-600">
                    Gerencie seus fornecedores de forma eficiente.
                </p>
            </div>

            <v-btn
                link
                to="/fornecedores/novo"
                color="primary"
                @click="abrirDialogCadastro"
            >
                + Adicionar Fornecedor
            </v-btn>
        </div>

        <v-data-table
            :headers="headers"
            :items="fornecedores"
            :page.sync="page"
            :loading="loading"
            v-model:page="page"
            loading-text="Carregando dados..."
            no-data-text="Nenhum fornecedor encontrado."
            class="elevation-1"
        >
            <template v-slot:item.status="{ item }">
                {{ item.ativo == 1 ? "Ativo" : "Inativo" }}
            </template>
            <template v-slot:item.preco_venda="{ item }">
                {{
                    Number(item.preco_venda).toLocaleString("pt-BR", {
                        style: "currency",
                        currency: "BRL",
                    })
                }}
            </template>
            <template v-slot:item.custo_medio="{ item }">
                {{
                    Number(item.custo_medio).toLocaleString("pt-BR", {
                        style: "currency",
                        currency: "BRL",
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
            <!-- Slot para customizar a paginação -->
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
                <v-card-title>Detalhes da Categoria</v-card-title>
                <v-card-text class="flex flex-col gap-3">
                    <p><strong>ID:</strong> {{ selectedItem.id }}</p>
                    <p><strong>Nome:</strong> {{ selectedItem.nome }}</p>
                    <p><strong>Email:</strong> {{ selectedItem.email }}</p>
                    <p>
                        <strong>Endereço:</strong>
                        {{ selectedItem.endereco }}
                    </p>
                    <p>
                        <strong>Telefone:</strong>
                        {{ parseTelefone(selectedItem.telefone) }}
                    </p>
                    <p>
                        <strong>Status:</strong>
                        {{ selectedItem.ativo == 1 ? "Ativo" : "Inativo" }}
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
                    <div>
                        <div class="pb-1"><strong>Descrição</strong></div>
                        <textarea
                            readonly
                            rows="3"
                            class="!bg-gray-100 w-full rounded-md !px-2 !py-1"
                            >{{ selectedItem.descricao }}</textarea
                        >
                    </div>
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
                    Tem certeza que deseja deletar o fornecedor "{{
                        selectedItem.nome
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
import FornecedorService from "../../services/FornecedorService";
import { parseTelefone } from "../../utils/parsers";

export default {
    data() {
        return {
            page: 1,
            totalPages: 1,
            loading: false,
            dialogVisualizar: false,
            dialogDeletar: false,
            selectedItem: {},
            parseTelefone,
            headers: [
                { title: "ID", value: "id" },
                { title: "Nome", value: "nome" },
                { title: "Email", value: "email" },
                { title: "Status", value: "status" },
                { title: "Ações", value: "actions", sortable: false },
            ],
            fornecedores: [],
        };
    },
    async created() {
        await this.carregarFornecedores();
    },
    methods: {
        async carregarFornecedores() {
            try {
                this.loading = true;
                const response = await FornecedorService.listar();

                this.fornecedores = response.data.data.data;
                this.page = response.data.data.current_page;
                this.totalPages = response.data.data.last_page;
            } catch (error) {
                console.error("Erro ao carregar fornecedores:", error);
                alert("Erro ao carregar fornecedores");
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
                await FornecedorService.excluir(this.selectedItem.id);
                this.fornecedores = this.fornecedores.filter(
                    (c) => c.id !== this.selectedItem.id
                );
                this.dialogDeletar = false;

                this.$snackbar.show({
                    message: "Fornecedor deletado com sucesso",
                    color: "success",
                });
            } catch (error) {
                console.error("Erro ao deletar fornecedor:", error);
                alert("Erro ao deletar fornecedor");
            }
        },
        editar(item) {
            this.$router.push(`/fornecedores/editar/${item.id}`);
        },
        abrirDialogCadastro(modo, fornecedor = null) {
            this.modoAtual = modo;
            this.selectedItem = fornecedor || null;
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
