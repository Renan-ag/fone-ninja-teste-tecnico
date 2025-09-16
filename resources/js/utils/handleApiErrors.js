export function handleApiFormErrors(errorKey) {
    switch (errorKey) {
        case "duplicate":
            return "Já existe um item semelhante cadastrado no sistema.";
        case "null_violation":
            return "Um campo obrigatório está vazio.";
        case "too_long":
            return "Um campo excede o limite de caracteres permitido.";
        default:
            return "Ocorreu um erro ao processar a solicitação. Por favor, tente novamente.";
    }
}
