import { Tabs } from "expo-router";
import { Platform } from "react-native";
import { BlurView } from "expo-blur";
import { AppIcon } from "@/components/ui/AppIcon";

export default function TabLayout() {
  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: "#007AFF",
        tabBarInactiveTintColor: "#8E8E93",
        tabBarLabelStyle: {
          fontFamily: "Poppins",
          fontSize: 11,
        },
        tabBarShowLabel: true,
        tabBarItemStyle: {
          paddingTop: 6,
        },
        tabBarStyle: {
          position: "absolute",
          bottom: 24,
          marginHorizontal: 40,
          height: 64,
          borderRadius: 32,
          borderTopWidth: 0,
          elevation: 8,
          backgroundColor: Platform.OS === "android" ? "rgba(255,255,255,0.95)" : "transparent",
          shadowColor: "#000",
          shadowOffset: { width: 0, height: 8 },
          shadowOpacity: 0.15,
          shadowRadius: 16,
        },
        tabBarBackground: () =>
          Platform.OS === "ios" ? (
            <BlurView
              intensity={80}
              tint="light"
              style={{
                flex: 1,
                borderRadius: 32,
                overflow: "hidden",
              }}
            />
          ) : null,
      }}
    >
      <Tabs.Screen
        name="index"
        options={{
          title: "Home",
          tabBarIcon: ({ color, size }) => <AppIcon name="home" color={color} size={size} />,
        }}
      />
      <Tabs.Screen
        name="favorites"
        options={{
          title: "Favorites",
          tabBarIcon: ({ color, size }) => <AppIcon name="heart" color={color} size={size} />,
        }}
      />
      <Tabs.Screen
        name="alerts"
        options={{
          title: "Alerts",
          tabBarIcon: ({ color, size }) => <AppIcon name="bell" color={color} size={size} />,
        }}
      />
    </Tabs>
  );
}
