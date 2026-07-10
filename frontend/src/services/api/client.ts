import axios from "axios";

// En Expo, define EXPO_PUBLIC_API_URL en tu .env (necesita el prefijo
// EXPO_PUBLIC_ para que el bundler lo exponga al cliente).
// Ojo con el host durante desarrollo:
//   - Simulador iOS -> localhost funciona tal cual
//   - Emulador Android -> usa 10.0.2.2 en vez de localhost
//   - Dispositivo físico -> usa la IP local de tu ordenador (ej. 192.168.x.x)
export const apiClient = axios.create({
    baseURL: process.env.EXPO_PUBLIC_API_URL ?? "http://localhost:8000/api",
    timeout: 10000,
});