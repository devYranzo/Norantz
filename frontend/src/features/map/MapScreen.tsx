import { useCallback, useEffect, useRef, useState } from "react";
import MapView, { Region } from "react-native-maps";

import { CenterLocationButton } from "./components/CenterLocationButton";
import { useCurrentLocation } from "./hooks/useCurrentLocation";
import { StopMarkers } from "@/features/stops/components/StopMarkers";
import { useStops } from "@/features/stops/hooks/useStops";
import { useVisibleStops } from "@/features/stops/hooks/useVisibleStops";
import { TransitStop } from "@/types/transit";

export function MapScreen() {
    const mapRef = useRef<MapView>(null);

    const { region: initialRegion, refresh: refreshLocation } = useCurrentLocation();
    const [region, setRegion] = useState<Region | null>(null);
    const [selectedStopId, setSelectedStopId] = useState<string | null>(null);

    // useState(initialRegion) solo lee el valor en el primer render; como
    // useCurrentLocation resuelve la ubicación de forma asíncrona, ese valor
    // inicial es siempre null y nunca se vuelve a sincronizar. Este efecto
    // sí reacciona cuando initialRegion pasa de null a un valor real.
    useEffect(() => {
        if (initialRegion && !region) {
            setRegion(initialRegion);
        }
    }, [initialRegion, region]);

    const { stops } = useStops(region);
    // Filtro extra en cliente: por el debounce de useStops, las paradas que
    // tenemos en memoria pueden ser de un region ligeramente distinto al
    // actual (por ejemplo si el usuario ha movido el mapa muy rápido).
    const visibleStops = useVisibleStops(stops, region);

    const handleStopPress = useCallback((stop: TransitStop) => {
        setSelectedStopId((current) => (current === stop.id ? null : stop.id));
    }, []);

    const centerOnUser = useCallback(async () => {
        const freshRegion = await refreshLocation();
        if (freshRegion) {
            setRegion(freshRegion);
            mapRef.current?.animateToRegion(freshRegion, 500);
        }
    }, [refreshLocation]);

    if (!region) {
        return null;
    }

    return (
        <>
            <MapView
                ref={mapRef}
                style={{ flex: 1 }}
                initialRegion={initialRegion ?? undefined}
                onRegionChangeComplete={setRegion}
                showsUserLocation
            >
                <StopMarkers
                    stops={visibleStops}
                    selectedStopId={selectedStopId}
                    onStopPress={handleStopPress}
                />
            </MapView>
            <CenterLocationButton onPress={centerOnUser} />
        </>
    );
}