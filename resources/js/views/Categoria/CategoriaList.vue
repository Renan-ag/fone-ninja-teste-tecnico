<template>
    <v-container>
        <div class="mb-4 flex justify-between gap-2 flex-col md:flex-row">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Categorias</h1>
                <p class="text-gray-600">
                    Gerencie suas categorias de produtos de forma eficiente.
                </p>
            </div>

            <v-btn
                link
                to="/categorias/novo"
                color="primary"
                @click="abrirDialogCadastro"
            >
                + Adicionar Categoria
            </v-btn>
        </div>

        <v-data-table
            :headers="headers"
            :items="categorias"
            :page.sync="page"
            :loading="loading"
            v-model:page="page"
            loading-text="Carregando dados..."
            class="elevation-1"
        >
            <template v-slot:item.status="{ item }">
                {{ item.ativo == 1 ? "Ativo" : "Inativo" }}
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
                <v-card-text>
                    <p><strong>ID:</strong> {{ selectedItem.id }}</p>
                    <p><strong>Nome:</strong> {{ selectedItem.nome }}</p>
                    <p>
                        <strong>Status:</strong>
                        {{ selectedItem.ativo == 1 ? "Ativo" : "Inativo" }}
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
                    Tem certeza que deseja deletar a categoria "{{
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
import CategoriaService from "@/services/CategoriaService";

export default {
    data() {
        return {
            page: 1,
            totalPages: 1,
            loading: false,
            dialogVisualizar: false, // Inicializado como false
            dialogDeletar: false, // Inicializado como false
            selectedItem: {},
            headers: [
                { title: "ID", value: "id" },
                { title: "Nome", value: "nome" },
                { title: "Status", value: "status" },
                { title: "Ações", value: "actions", sortable: false },
            ],
            categorias: [],
        };
    },
    async created() {
        await this.carregarCategorias();
    },
    methods: {
        async carregarCategorias() {
            try {
                this.loading = true;
                const response = await CategoriaService.listar();

                this.categorias = response.data.data.data;
                this.page = response.data.data.current_page;
                this.totalPages = response.data.data.last_page;
            } catch (error) {
                console.error("Erro ao carregar categorias:", error);
                alert("Erro ao carregar categorias");
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
                await CategoriaService.excluir(this.selectedItem.id); // Supondo que o serviço tenha um método deletar
                this.categorias = this.categorias.filter(
                    (c) => c.id !== this.selectedItem.id
                );
                this.dialogDeletar = false;

                this.$snackbar.show({
                    message: "Categoria deletada com sucesso",
                    color: "success",
                });
            } catch (error) {
                console.error("Erro ao deletar categoria:", error);
            }
        },
        editar(item) {
            this.$router.push(`/categorias/editar/${item.id}`);
        },
        abrirDialogCadastro(modo, categoria = null) {
            this.modoAtual = modo;
            this.categoriaSelecionada = categoria || null;
            this.dialog = true;
        },
        fecharDialog() {
            this.dialog = false;
            this.modoAtual = "create";
            this.categoriaSelecionada = null;
        },
    },
};
</script>
