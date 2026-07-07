import { useRef } from "react";
import MapView, {Region} from "react-native-maps";

import { CenterLocationButton } from "./components/CenterLocationButton";
import { useCurrentLocation } from "./hooks/useCurrentLocation";

export function MapScreen() {
  const mapRef = useRef<MapView>(null);

  const region = useCurrentLocation();

  if (!region) {
    return null;
  }

  function centerOnUser() {
    mapRef.current?.animateToRegion(region as Region, 500);
  }

  return (
    <>
      <MapView ref={mapRef} style={{ flex: 1 }} initialRegion={region} showsUserLocation />

      <CenterLocationButton onPress={centerOnUser} />
    </>
  );
}
