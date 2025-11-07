import { useState, useCallback } from "react";

export const useMessage = () => {
  const [msg, setMsg] = useState("");

  const showMessage = useCallback((message, autoHide = true) => {
    setMsg(message);
    if (autoHide) {
      setTimeout(() => setMsg(""), 3000);
    }
  }, []);

  const clearMessage = useCallback(() => {
    setMsg("");
  }, []);

  return {
    msg,
    showMessage,
    clearMessage,
  };
};
