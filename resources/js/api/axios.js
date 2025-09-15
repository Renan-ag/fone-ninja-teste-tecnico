import axios from "axios";

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || "/api", // Use variáveis de ambiente para flexibilidade
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
});

export default api;
