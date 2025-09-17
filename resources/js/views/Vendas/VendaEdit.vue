<template>
    <v-container>
        <venda-form
            :modo="modo"
            :venda="venda"
            @salvo="handleSalvo"
            @cancelar="handleCancelar"
            @erro="handleErro"
        />
    </v-container>
</template>

<script>
import VendaForm from "@/components/forms/VendaForm.vue";
import VendaService from "@/services/VendaService";

export default {
    components: { VendaForm },
    data() {
        return {
            modo: "create",
            venda: {},
        };
    },
    async created() {
        if (this.$route.params.id) {
            this.modo = "edit";
            await this.carregarVenda(); 
        }
    },
    methods: {
        async carregarVenda() {
            try {
                const response = await VendaService.visualizar(
                    this.$route.params.id
                );

                this.venda = response.data.data;
            } catch (error) {
                console.error("Erro ao carregar venda:", error);

            }
        },
        handleSalvo() {
            this.$router.push("/vendas");
        },
        handleCancelar() {
            this.$router.push("/vendas");
        },
        handleErro(error) {
            console.error("Erro no formulário:", error);
            alert("Houve um erro ao salvar a venda.");
        },
    },
};
</script>
