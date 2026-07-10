import { View, StyleSheet } from "react-native";
import { Marker } from "react-native-maps";
import { MaterialCommunityIcons } from "@expo/vector-icons";

import { TransitStop, TransitMode } from "@/types/transit";

const NEUTRAL_COLOR = "#374151";

type StopMarkerProps = {
    stop: TransitStop;
    selected?: boolean;
    onPress?: (stop: TransitStop) => void;
};

function getStopMode(stop: TransitStop): TransitMode {
    return stop.modes?.[0] ?? "bus";
}

function getStopIcon(mode: TransitMode) {
    switch (mode) {
        case "tram":
            return "train";
        case "night_bus":
        case "bus":
        default:
            return "bus-stop";
    }
}

export function StopMarker({stop, selected = false, onPress}: StopMarkerProps) {
    const mode = getStopMode(stop);

    return (
        <Marker
            coordinate={{
                latitude: stop.latitude,
                longitude: stop.longitude,
            }}
            onPress={() => onPress?.(stop)}
        >
            <View
                style={[
                    styles.marker,
                    {
                        backgroundColor: selected ? NEUTRAL_COLOR : "#FFFFFF",
                        borderColor: selected ? NEUTRAL_COLOR : "#D1D5DB",
                    },
                ]}
            >
                <MaterialCommunityIcons
                    name={getStopIcon(mode)}
                    size={16}
                    color={selected ? "#FFFFFF" : NEUTRAL_COLOR}
                />
            </View>
        </Marker>
    );
}

const styles = StyleSheet.create({
    marker: {
        width: 28,
        height: 28,
        borderRadius: 14,

        justifyContent: "center",
        alignItems: "center",

        borderWidth: 1,
    },
});