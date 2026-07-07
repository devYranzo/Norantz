import { useMemo } from "react";
import { Region } from "react-native-maps";

import { TransitStop } from "@/types/transit";

export function useVisibleStops(
    stops: TransitStop[],
    region: Region | null
) {
    return useMemo(() => {
        if (!region) return [];

        const minLat = region.latitude - region.latitudeDelta / 2;
        const maxLat = region.latitude + region.latitudeDelta / 2;

        const minLng = region.longitude - region.longitudeDelta / 2;
        const maxLng = region.longitude + region.longitudeDelta / 2;

        return stops.filter(
            (stop) =>
                stop.latitude >= minLat &&
                stop.latitude <= maxLat &&
                stop.longitude >= minLng &&
                stop.longitude <= maxLng
        );
    }, [stops, region]);
}