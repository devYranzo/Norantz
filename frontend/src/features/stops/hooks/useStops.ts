import { useEffect, useState } from "react";
import { Region } from "react-native-maps";
import axios from "axios";

import { apiClient } from "@/services/api/client";
import { TransitStop } from "@/types/transit";
import { regionToBoundingBox } from "@/utils/regionToBoundingBox";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";

// Laravel envuelve las colecciones de Resource en { data: [...] } por
// defecto, así que la respuesta del endpoint tiene esta forma, no
// directamente un array.
type StopsResponse = {
    data: TransitStop[];
};

export function useStops(region: Region | null) {
    const [stops, setStops] = useState<TransitStop[]>([]);
    const [error, setError] = useState<Error | null>(null);

    // Evita disparar una petición en cada pequeño movimiento del mapa
    const debouncedRegion = useDebouncedValue(region, 400);

    useEffect(() => {
        if (!debouncedRegion) return;

        const controller = new AbortController();

        apiClient
            .get<StopsResponse>("/stops", {
                params: regionToBoundingBox(debouncedRegion),
                signal: controller.signal,
            })
            .then((response) => {
                setStops(response.data.data);
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