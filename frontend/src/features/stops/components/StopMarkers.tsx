import { TransitStop } from "@/types/transit";
import { StopMarker } from "./StopMarker";

type StopMarkersProps = {
    stops: TransitStop[];
    selectedStopId: string | null;
    onStopPress?: (stop: TransitStop) => void;
};

export function StopMarkers({stops, selectedStopId, onStopPress,}: StopMarkersProps) {
    return (
        <>
            {stops.map((stop) => (
                <StopMarker
                    key={stop.id}
                    stop={stop}
                    selected={stop.id === selectedStopId}
                    onPress={onStopPress}
                />
            ))}
        </>
    );
}