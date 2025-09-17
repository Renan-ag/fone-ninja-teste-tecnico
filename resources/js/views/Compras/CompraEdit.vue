<template>
    <v-container>
        <compra-form
            :modo="modo"
            :compra="compra"
            @salvo="handleSalvo"
            @cancelar="handleCancelar"
            @erro="handleErro"
        />
    </v-container>
</template>

<script>
import CompraForm from "@/components/forms/CompraForm.vue";
import CompraService from "@/services/CompraService";

export default {
    components: { CompraForm },
    data() {
        return {
            modo: "create",
            compra: {},
        };
    },
    async created() {
        if (this.$route.params.id) {
            this.modo = "edit";
            await this.carregarCompra();
        }
    },
    methods: {
        async carregarCompra() {
            try {
                const response = await CompraService.visualizar(
                    this.$route.params.id
                );

                this.compra = response.data.data;
            } catch (error) {
                console.error("Erro ao carregar compra:", error);
            }
        },
        handleSalvo() {
            this.$router.push("/compras");
        },
        handleCancelar() {
            this.$router.push("/compras");
        },
        handleErro(error) {
            console.error("Erro no formulário:", error);
            alert("Houve um erro ao salvar a compra.");
        },
    },
};
</script>
