<template>
    <v-container>
        <produto-form
            :modo="modo"
            :produto="produto"
            @salvo="handleSalvo"
            @cancelar="handleCancelar"
            @erro="handleErro"
        />
    </v-container>
</template>

<script>
import ProdutoForm from "@/components/forms/ProdutoForm.vue";
import ProdutoService from "@/services/ProdutoService";

export default {
    components: { ProdutoForm },
    data() {
        return {
            modo: "create",
            produto: {},
        };
    },
    async created() {
        if (this.$route.params.id) {
            this.modo = "edit";
            await this.carregarProduto();            
        }
    },
    methods: {
        async carregarProduto() {
            try {
                const response = await ProdutoService.visualizar(
                    this.$route.params.id
                );
                
                this.produto = response.data.data;
            } catch (error) {
                console.error("Erro ao carregar produto:", error);
            }
        },
        handleSalvo() {
            this.$router.push("/produtos");
        },
        handleCancelar() {
            this.$router.push("/produtos");
        },
        handleErro(error) {
            console.error("Erro no formulário:", error);            
            alert("Houve um erro ao salvar o produto.");
        },
    },
};
</script>
