export function parsePreco(valor) {
    if (!valor) return 0;
    return Number(valor).toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL",
    });
}

export function parseTelefone(telefone) {
    telefone = telefone.replace(/\D/g, "");

    if (telefone.length === 11) {
        return telefone.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
    }

    if (telefone.length === 10) {
        return telefone.replace(/(\d{2})(\d{4})(\d{4})/, "($1) $2-$3");
    }

    return telefone;
}
