<template>
    <v-container>
        <categoria-form
            :modo="modo"
            :categoria="categoria"
            @salvo="handleSalvo"
            @cancelar="handleCancelar"
            @erro="handleErro"
        />
    </v-container>
</template>

<script>
import CategoriaForm from "@/components/forms/CategoriaForm.vue";
import CategoriaService from "@/services/CategoriaService";

export default {
    components: { CategoriaForm },
    data() {
        return {
            modo: "create",
            categoria: {},
        };
    },
    async created() {
        if (this.$route.params.id) {
            this.modo = "edit";
            await this.carregarCategoria();
        }
    },
    methods: {
        async carregarCategoria() {
            try {
                const response = await CategoriaService.visualizar(
                    this.$route.params.id
                );

                this.categoria = response.data.data;
            } catch (error) {
                console.error("Erro ao carregar categoria:", error);
            }
        },
        handleSalvo() {
            this.$router.push("/categorias");
        },
        handleCancelar() {
            this.$router.push("/categorias");
        },
        handleErro(error) {
            console.error("Erro no formulário:", error);
            alert("Houve um erro ao salvar a categoria.");
        },
    },
};
</script>
