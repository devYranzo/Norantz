import MaterialCommunityIcons from "@expo/vector-icons/MaterialCommunityIcons";
import { ComponentProps } from "react";

type MaterialIconName = ComponentProps<typeof MaterialCommunityIcons>["name"];

type AppIconProps = {
  name: MaterialIconName;
  size?: number;
  color: string;
};

export function AppIcon({ name, size = 24, color }: AppIconProps) {
  return <MaterialCommunityIcons name={name} size={size} color={color} />;
}
