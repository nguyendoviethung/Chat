import { useEffect, useState } from "react";
import "./ChatRoom.css";

export default function ChatRoom() {
  const [messages, setMessages] = useState([]);
  const [input, setInput] = useState("");
  const username = localStorage.getItem("username") || "Guest";
  const [ws, setWs] = useState(null);

  useEffect(() => {
    const socket = new WebSocket("ws://localhost:9000");

    socket.onopen = () => {
      console.log(" Kết nối WebSocket thành công");
      socket.send(JSON.stringify({ type: "join", user: username }));
    };

    socket.onmessage = (event) => {
      const msg = JSON.parse(event.data);
      setMessages((prev) => [...prev, msg]);
      
    };

    socket.onclose = () => {
      console.log("Mất kết nối WebSocket");
    };

    setWs(socket);
    return () => socket.close();
  }, [username]);

  const sendMessage = () => {
    if (ws && input.trim()) {
      ws.send(JSON.stringify({ type: "message", user: username, text: input }));
      setInput("");
    }
  };

  return (
    <div className="chat-wrapper">
      <h2 className="chat-title"> Phòng Chat</h2>

      <div className="chat-box">
        {messages.map((m, i) => (
          <div
            key={i}
            className={`chat-message ${
              m.user === username ? "my-message" : "other-message"
            }`}
          >
            <strong>{m.user}:</strong> {m.text}
          </div>
        ))}
      </div>

      <div className="chat-input-area">
        <input
          type="text"
          className="chat-input"
          placeholder="Nhập tin nhắn..."
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={(e) => e.key === "Enter" && sendMessage()}
        />
        <button onClick={sendMessage} className="chat-button">
          Gửi
        </button>
      </div>
    </div>
  );
}
