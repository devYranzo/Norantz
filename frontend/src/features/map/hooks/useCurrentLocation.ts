import { useCallback, useEffect, useRef, useState } from "react";
import * as Location from "expo-location";
import { Region } from "react-native-maps";

import { DEFAULT_REGION } from "@/constants/map";

export function useCurrentLocation() {
  const [region, setRegion] = useState<Region | null>(null);
  const isMountedRef = useRef(true);

  useEffect(() => {
    return () => {
      isMountedRef.current = false;
    };
  }, []);

  const fetchLocation = useCallback(async (): Promise<Region | null> => {
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();

      if (status !== "granted") {
        if (isMountedRef.current) setRegion(DEFAULT_REGION);
        return DEFAULT_REGION;
      }

      const location = await Location.getCurrentPositionAsync({
        accuracy: Location.Accuracy.Balanced,
      });

      const nextRegion: Region = {
        latitude: location.coords.latitude,
        longitude: location.coords.longitude,
        latitudeDelta: 0.006,
        longitudeDelta: 0.006,
      };

      if (isMountedRef.current) setRegion(nextRegion);
      return nextRegion;
    } catch {
      if (isMountedRef.current) setRegion(DEFAULT_REGION);
      return DEFAULT_REGION;
    }
  }, []);

  // Petición inicial al montar el hook.
  useEffect(() => {
    fetchLocation();
  }, [fetchLocation]);

  // region: la última ubicación conocida (para el estado inicial del mapa).
  // refresh: vuelve a pedir la ubicación en el momento, para el botón de
  // "centrar en mi ubicación" (no debe depender del viewport actual del mapa).
  return { region, refresh: fetchLocation };
}