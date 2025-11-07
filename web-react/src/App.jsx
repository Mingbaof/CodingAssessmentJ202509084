import { useEffect } from "react";
import { useAuth, useSync, useMessage } from "./hooks";

const App = () => {
  const { configured, connected, checkStatus } = useAuth();
  const { loading, accounts, vendors, syncAccounts, syncVendors } = useSync();
  const { msg, showMessage } = useMessage();

  useEffect(() => {
    const handleStatusCheck = async () => {
      try {
        const result = await checkStatus();
        if (result.error) {
          showMessage(result.error, false);
        }
      } catch (error) {
        showMessage("Failed to check connection status", false);
      }
    };

    handleStatusCheck();
  }, [checkStatus, showMessage]);

  const handleSync = async (syncFunction, type) => {
    showMessage("Syncing…", false);
    try {
      const result = await syncFunction();
      showMessage(`Synced ${result.count} ${type} records`, false);
    } catch (error) {
      showMessage(`Error: ${error.message}`, false);
    }
  };


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
      </div>

      {/* show error message */}
      {!configured && msg && (
        <div
          style={{
            backgroundColor: "#fff3cd",
            border: "1px solid #ffeaa7",
            borderRadius: 8,
            padding: 16,
            marginBottom: 16,
            color: "#856404",
          }}
        >
          <strong>Configuration Required:</strong>
          <br />
          {msg}
        </div>
      )}

      {/* show info message */}
      {configured && msg && (
        <div
          style={{
            backgroundColor: "#d4edda",
            border: "1px solid #c3e6cb",
            borderRadius: 8,
            padding: 16,
            marginBottom: 16,
            color: "#155724",
          }}
        >
          {msg}
        </div>
      )}

      <div style={{ display: "flex", gap: 12, marginBottom: 24 }}>
        <button
          disabled={!connected || loading}
          onClick={() => handleSync(syncAccounts, "account")}
        >
          Sync Accounts
        </button>
        <button
          disabled={!connected || loading}
          onClick={() => handleSync(syncVendors, "vendor")}
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
};

export default App;

const Table = ({ rows }) => {
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
};
