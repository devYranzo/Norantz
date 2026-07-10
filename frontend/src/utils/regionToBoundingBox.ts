import { Region } from "react-native-maps";

export type BoundingBox = {
    min_lat: number;
    max_lat: number;
    min_lng: number;
    max_lng: number;
};

export function regionToBoundingBox(region: Region): BoundingBox {
    return {
        min_lat: region.latitude - region.latitudeDelta / 2,
        max_lat: region.latitude + region.latitudeDelta / 2,
        min_lng: region.longitude - region.longitudeDelta / 2,
        max_lng: region.longitude + region.longitudeDelta / 2,
    };
}