import { useCallback, useRef, useState } from "react";
import MapView, { Region } from "react-native-maps";

import { CenterLocationButton } from "./components/CenterLocationButton";
import { useCurrentLocation } from "./hooks/useCurrentLocation";
import { StopMarkers } from "@/features/stops/components/StopMarkers";
import { useVisibleStops } from "@/features/stops/hooks/useVisibleStops";
import { TransitStop } from "@/types/transit";

// TODO: sustituir por los datos reales cuando exista el backend
// (fetch / hook tipo useStops() que devuelva TransitStop[])
const stops: TransitStop[] = [];

export function MapScreen() {
  const mapRef = useRef<MapView>(null);

  const initialRegion = useCurrentLocation();
  const [region, setRegion] = useState<Region | null>(initialRegion);
  const [selectedStopId, setSelectedStopId] = useState<string | null>(null);

  const visibleStops = useVisibleStops(stops, region);

  const handleStopPress = useCallback((stop: TransitStop) => {
    setSelectedStopId((current) => (current === stop.id ? null : stop.id));
  }, []);

  function centerOnUser() {
    if (!region) return;
    mapRef.current?.animateToRegion(region, 500);
  }

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