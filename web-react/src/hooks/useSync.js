import { useState, useCallback } from "react";

const API = "http://localhost:8080";

export const useSync = () => {
  const [loading, setLoading] = useState(false);
  const [accounts, setAccounts] = useState([]);
  const [vendors, setVendors] = useState([]);

  const sync = useCallback(async (path, setter) => {
    setLoading(true);
    try {
      const res = await fetch(`${API}${path}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
      });
      const j = await res.json();
      const rows = j.rows || [];
      setter(rows);
      // Return both API count and actual array length for reliability
      return { count: j.count ?? rows.length };
    } catch (error) {
      console.error("Sync failed:", error);
      throw error;
    } finally {
      setLoading(false);
    }
  }, []);

  const syncAccounts = useCallback(
    () => sync("/sync/accounts", setAccounts),
    [sync]
  );

  const syncVendors = useCallback(
    () => sync("/sync/vendors", setVendors),
    [sync]
  );

  return {
    loading,
    accounts,
    vendors,
    syncAccounts,
    syncVendors,
  };
};
