import { useEffect, useState } from "react";

const API = "http://localhost:8080";

export default function App() {
  // should move those into hooks
  const [configured, setConfigured] = useState(false);
  const [connected, setConnected] = useState(false);
  const [loading, setLoading] = useState(false);
  const [accounts, setAccounts] = useState([]);
  const [vendors, setVendors] = useState([]);
  const [msg, setMsg] = useState("");

  async function checkStatus() {
    const res = await fetch(`${API}/auth/status`, { credentials: "include" });
    const j = await res.json();
    setConfigured(j.configured);
    setConnected(j.connected);
    if (j.error) setMsg(j.error);
  }

  useEffect(() => {
    checkStatus();
  }, []);

  async function sync(path, setter) {
    setLoading(true);
    setMsg("Syncing…");
    try {
      const res = await fetch(`${API}${path}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
      });
      const j = await res.json();
      setter(j.rows || []);
      setMsg(`Synced ${j.count || 0} records`);
    } catch (e) {
      setMsg("Error: " + e.message);
    } finally {
      setLoading(false);
      setTimeout(() => setMsg(""), 3000);
    }
  }


  return (
    <div
      style={{
        fontFamily: "system-ui, sans-serif",
        padding: 24,
        maxWidth: 1000,
        margin: "0 auto",
      }}
    >
      <h1 style={{ marginBottom: 8 }}>Coding Assessment</h1>
      <p style={{ color: "#555" }}>
        Fetching Accounts and Vendors from demo company on Xero.
        Connection.
      </p>

      <div
        style={{
          display: "flex",
          gap: 12,
          alignItems: "center",
          marginBottom: 16,
        }}
      >
        <button onClick={checkStatus}>Test Connection</button>
        <span style={{ color: connected ? "green" : "crimson" }}>
          {configured
            ? connected
              ? "Connected"
              : "Configured — not connected"
            : "Not configured"}
        </span>
        {msg && <span style={{ marginLeft: 12 }}>{msg}</span>}
      </div>

      <div style={{ display: "flex", gap: 12, marginBottom: 24 }}>
        <button
          disabled={!connected || loading}
          onClick={() => sync("/sync/accounts", setAccounts)}
        >
          Sync Accounts
        </button>
        <button
          disabled={!connected || loading}
          onClick={() => sync("/sync/vendors", setVendors)}
        >
          Sync Vendors
        </button>
      </div>

      <h2>Accounts ({accounts.length})</h2>
      <Table rows={accounts} />

      <h2 style={{ marginTop: 28 }}>Vendors ({vendors.length})</h2>
      <Table rows={vendors} />
    </div>
  );
}

function Table({ rows }) {
  if (!rows?.length) return <div style={{ color: "#777" }}>No data</div>;
  const headers = Object.keys(rows[0]);
  return (
    <div
      style={{ overflowX: "auto", border: "1px solid #ddd", borderRadius: 8 }}
    >
      <table style={{ borderCollapse: "collapse", width: "100%" }}>
        <thead>
          <tr>
            {headers.map((h) => (
              <th
                key={h}
                style={{
                  textAlign: "left",
                  padding: 8,
                  borderBottom: "1px solid #eee",
                }}
              >
                {h}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((r, i) => (
            <tr key={i}>
              {headers.map((h) => (
                <td
                  key={h}
                  style={{ padding: 8, borderBottom: "1px solid #f5f5f5" }}
                >
                  {String(r[h] ?? "")}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
