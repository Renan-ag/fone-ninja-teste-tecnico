<template>
    <div>
        <h2 class="mb-4 text-2xl font-bold">
            {{ modo === "create" ? "Criar Fornecedor" : "Editar Fornecedor" }}
        </h2>

        <v-form
            ref="form"
            v-model="valid"
            lazy-validation
            class="form-container flex flex-col md:grid md:grid-cols-2 gap-2"
        >
            <v-text-field
                v-model="form.nome"
                label="Nome *"
                :rules="[
                    (v) => !!v || 'Nome é obrigatório',
                    (v) =>
                        v.length <= 200 ||
                        'O nome deve ter no máximo 200 caracteres',
                ]"
                :error-messages="api_error?.nome"
                required
                outlined
            />

            <v-text-field
                v-model="form.email"
                type="email"
                label="Email"
                :rules="[
                    (v) =>
                        v.length <= 200 ||
                        'O email deve ter no máximo 200 caracteres',
                ]"
                :error-messages="api_error?.email"
                required
                outlined
            />

            <v-text-field
                v-model="form.telefone"
                label="Telefone"
                v-mask="['(##) #####-####', '(##) ####-####']"
                :rules="[
                    (v) =>
                        v.length <= 20 ||
                        'O telefone deve ter no máximo 20 caracteres',
                ]"
                :error-messages="api_error?.telefone"
                outlined
            />

            <v-text-field
                v-model="form.endereco"
                label="Endereço"
                type="text"
                :rules="[
                    (v) =>
                        v.length <= 500 ||
                        'O endereço deve ter no máximo 500 caracteres',
                ]"
                :error-messages="api_error?.endereco"
                outlined
            />

            <v-textarea
                v-model="form.descricao"
                class="col-span-2"
                label="Descrição"
                :rules="[
                    (v) =>
                        v.length <= 200 ||
                        'A descrição deve ter no máximo 200 caracteres',
                ]"
                :error-messages="api_error?.descricao"
                outlined
            />

            <v-switch
                v-model="form.ativo"
                :true-value="1"
                :false-value="0"
                :label="`Status: ${form.ativo == 1 ? 'Ativo' : 'Inativo'}`"
                :error-messages="api_error?.ativo"
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
import { parsePreco } from "@/utils/parsers";

import FornecedorService from "@/services/FornecedorService";

export default {
    props: {
        modo: {
            type: String,
            default: "create",
            validator: (value) => ["create", "edit"].includes(value),
        },
        fornecedor: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            valid: true,
            loading: false,
            api_error: {},
            form: {
                id: null,
                nome: "",
                email: "",
                telefone: "",
                endereco: "",
                descricao: "",
                ativo: 1,
            },
            fornecedorSelecionado: null,
            parsePreco,
        };
    },
    watch: {
        fornecedor: {
            handler(newFornecedor) {
                if (this.modo === "edit" && newFornecedor && newFornecedor.id) {
                    this.form = {
                        id: newFornecedor.id,
                        nome: newFornecedor.nome || "",
                        email: newFornecedor.email || "",
                        telefone: newFornecedor.telefone || "",
                        endereco: newFornecedor.endereco || "",
                        descricao: newFornecedor.descricao || "",
                        ativo: Number(newFornecedor.ativo) == 1 ? 1 : 0,
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
                    const payload = {
                        ...this.form,
                        telefone: this.form.telefone.replace(/\D/g, ""),
                    };
                    if (this.modo === "create") {
                        await FornecedorService.cadastrar(payload);
                        this.$emit("salvo");
                    } else {
                        await FornecedorService.atualizar(
                            this.form.id,
                            payload
                        );
                        this.$emit("salvo");
                    }
                } catch (error) {
                    console.error(
                        `Erro ao ${
                            this.modo === "create" ? "criar" : "atualizar"
                        } fornecedor:`,
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
