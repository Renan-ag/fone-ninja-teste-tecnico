<template>
    <div>
        <h2 class="mb-4 text-2xl font-bold">
            {{ modo === "create" ? "Criar Categoria" : "Editar Categoria" }}
        </h2>

        <v-form ref="form" v-model="valid" lazy-validation>
            <v-text-field
                v-model="form.nome"
                label="Nome *"
                :rules="[
                    (v) => !!v || 'Nome é obrigatório',
                    (v) =>
                        v.length <= 150 ||
                        'O nome deve ter no máximo 150 caracteres',
                ]"
                :error-messages="api_error?.nome"
                required
                outlined
            />
            <v-text-field v-model="form.descricao" label="Descrição" outlined />
            <v-switch
                v-model="form.ativo"
                :error-messages="api_error?.ativo"
                :true-value="1"
                :false-value="0"
                :label="`Status: ${form.ativo == 1 ? 'Ativo' : 'Inativo'}`"
            ></v-switch>
        </v-form>

        <div class="flex flex-col md:flex-row gap-2 justify-end">
            <v-btn color="grey" text @click="$emit('cancelar')">Cancelar</v-btn>
            <v-btn color="primary" :disabled="loading" @click="salvar"
                >Salvar</v-btn
            >
        </div>
    </div>
</template>

<script>
import CategoriaService from "@/services/CategoriaService";

export default {
    props: {
        modo: {
            type: String,
            default: "create",
            validator: (value) => ["create", "edit"].includes(value),
        },
        categoria: {
            type: Object,
            default: () => ({}),
        },
        api_error: null,
    },
    data() {
        return {
            valid: true,
            form: {
                id: null,
                nome: "",
                ativo: 0,
                descricao: "",
            },
            loading: false,
            api_error: {},
        };
    },
    watch: {
        categoria: {
            handler(newCategoria) {
                if (this.modo === "edit" && newCategoria && newCategoria.id) {
                    this.form = {
                        id: newCategoria.id,
                        nome: newCategoria.nome || "",
                        descricao: newCategoria.descricao || "",
                        ativo: Number(newCategoria.ativo) == 1 ? 1 : 0,
                    };
                }
            },
            immediate: true,
            deep: true,
        },
    },
    methods: {
        async salvar() {
            this.api_error = null;
            if (this.$refs.form.validate()) {
                this.loading = true;
                try {
                    if (this.modo === "create") {
                        await CategoriaService.cadastrar(this.form);
                        this.$emit("salvo");
                    } else {
                        await CategoriaService.atualizar(
                            this.form.id,
                            this.form
                        );
                        this.$emit("salvo");
                    }
                } catch (error) {
                    console.error(
                        `Erro ao ${
                            this.modo === "create" ? "criar" : "atualizar"
                        } categoria:`,
                        error
                    );
                    if (error.response.data.errors) {
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
