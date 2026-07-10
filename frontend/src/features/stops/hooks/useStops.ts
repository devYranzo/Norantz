import { useEffect, useState } from "react";
import { Region } from "react-native-maps";
import axios from "axios";

import { apiClient } from "@/services/api/client";
import { TransitStop } from "@/types/transit";
import { regionToBoundingBox } from "@/utils/regionToBoundingBox";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";

export function useStops(region: Region | null) {
    const [stops, setStops] = useState<TransitStop[]>([]);
    const [error, setError] = useState<Error | null>(null);

    // Evita disparar una petición en cada pequeño movimiento del mapa
    const debouncedRegion = useDebouncedValue(region, 400);

    useEffect(() => {
        if (!debouncedRegion) return;

        const controller = new AbortController();

        apiClient
            .get<TransitStop[]>("/stops", {
                params: regionToBoundingBox(debouncedRegion),
                signal: controller.signal,
            })
            .then((response) => {
                setStops(response.data);
                setError(null);
            })
            .catch((err) => {
                if (axios.isCancel(err)) return; // petición cancelada por un region nuevo, no es un error real
                setError(err instanceof Error ? err : new Error("Error al cargar las paradas"));
            });

        // Si el region cambia antes de que responda la petición anterior,
        // la cancelamos para no pisar datos nuevos con una respuesta vieja.
        return () => controller.abort();
    }, [debouncedRegion]);

    return { stops, error };
}