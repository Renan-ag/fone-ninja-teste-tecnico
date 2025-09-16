export function parsePreco(valor) {
    if (!valor) return 0;
    return (
        parseFloat(
            valor
                .toString()
                .replace(/[^\d,-]/g, "")
                .replace(",", ".")
        ) || 0
    );
}
