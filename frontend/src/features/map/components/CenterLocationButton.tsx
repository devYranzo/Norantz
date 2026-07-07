import { Pressable, StyleSheet } from "react-native";
import FontAwesome from "@expo/vector-icons/FontAwesome";

type LocateButtonProps = {
  onPress: () => void;
};

export function CenterLocationButton({ onPress }: LocateButtonProps) {
  return (
    <Pressable onPress={onPress} style={styles.container}>
      <FontAwesome name="location-arrow" size={24} color="black" />
    </Pressable>
  );
}

const styles = StyleSheet.create({
  container: {
    position: "absolute",

    right: 20,
    bottom: 120,

    width: 56,
    height: 56,

    borderRadius: 28,

    backgroundColor: "#FFF",

    justifyContent: "center",
    alignItems: "center",

    shadowColor: "#000",
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.12,
    shadowRadius: 10,

    elevation: 6,
  },
});
