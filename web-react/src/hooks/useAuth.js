import { useState, useCallback } from "react";

const API = "http://localhost:8080";

export const useAuth = () => {
  const [configured, setConfigured] = useState(false);
  const [connected, setConnected] = useState(false);

  const checkStatus = useCallback(async () => {
    try {
      const res = await fetch(`${API}/auth/status`, { credentials: "include" });
      const j = await res.json();
      setConfigured(j.configured);
      setConnected(j.connected);
      return j;
    } catch (error) {
      console.error("Failed to check auth status:", error);
      throw error;
    }
  }, []);

  return {
    configured,
    connected,
    checkStatus,
  };
};
