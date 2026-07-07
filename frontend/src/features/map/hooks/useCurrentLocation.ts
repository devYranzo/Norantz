import { useEffect, useState } from "react";
import * as Location from "expo-location";
import { Region } from "react-native-maps";

import { DEFAULT_REGION } from "@/constants/map";

export function useCurrentLocation() {
  const [region, setRegion] = useState<Region | null>(null);

  useEffect(() => {
    let mounted = true;

    async function loadLocation() {
      try {
        const { status } = await Location.requestForegroundPermissionsAsync();

        if (status !== "granted") {
          if (mounted) {
            setRegion(DEFAULT_REGION);
          }
          return;
        }

        const location = await Location.getCurrentPositionAsync({
          accuracy: Location.Accuracy.Balanced,
        });

        if (!mounted) return;

        setRegion({
          latitude: location.coords.latitude,
          longitude: location.coords.longitude,
          latitudeDelta: 0.006,
          longitudeDelta: 0.006,
        });
      } catch {
        if (mounted) {
          setRegion(DEFAULT_REGION);
        }
      }
    }

    loadLocation();

    return () => {
      mounted = false;
    };
  }, []);

  return region;
}
