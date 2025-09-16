<template>
    <v-container>
        <fornecedor-form
            :modo="modo"
            :fornecedor="fornecedor"
            @salvo="handleSalvo"
            @cancelar="handleCancelar"
            @erro="handleErro"
        />
    </v-container>
</template>

<script>
import FornecedorForm from "@/components/forms/FornecedorForm.vue";
import FornecedorService from "@/services/FornecedorService";

export default {
    components: { FornecedorForm },
    data() {
        return {
            modo: "create",
            fornecedor: {},
        };
    },
    async created() {
        if (this.$route.params.id) {
            this.modo = "edit";
            await this.carregarFornecedor();
        }
    },
    methods: {
        async carregarFornecedor() {
            try {
                const response = await FornecedorService.visualizar(
                    this.$route.params.id
                );
                
                this.fornecedor = response.data.data;
            } catch (error) {
                console.error("Erro ao carregar fornecedor:", error);
            }
        },
        handleSalvo() {
            this.$router.push("/fornecedores");
        },
        handleCancelar() {
            this.$router.push("/fornecedores");
        },
        handleErro(error) {
            console.error("Erro no formulário:", error);
            alert("Houve um erro ao salvar o fornecedor.");
        },
    },
};
</script>
