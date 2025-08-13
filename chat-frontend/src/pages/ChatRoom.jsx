  import { useEffect, useState } from "react";
  import axios from "axios";
  import "./ChatRoom.css";

  export default function ChatRoom({onClose, friend_id, friendName}) {
    
    const [messages, setMessages] = useState([]);
    const [input, setInput] = useState("");
    const username = localStorage.getItem("username");  // Lấy tên người dùng từ localStorage
    const token = localStorage.getItem(`token_${username}`); // Lấy token từ localStorage với khóa duy nhất cho từng người dùng
    const [ws, setWs] = useState(null);

    useEffect(() => {
 
     const messageSent = async () => {
     try {
      const result = await axios.get("http://localhost:8080/message-sent.php", {
          token : token,
          friend_id: friend_id
      });
      setMessages(result.data); // Lưu dữ liệu tin nhắn vào state
      } catch (error) {
        console.error("Lỗi khi lấy tin nhắn:", error);
     }
  };
    
    messageSent(); // Lấy tin nhắn đã nhắn 

      // Kết nối Websocket
      const socket = new WebSocket("ws://localhost:9000");

      socket.onopen = () => {
        console.log(" Kết nối WebSocket thành công");
        socket.send(JSON.stringify({ 
          type: "join", 
          user_id: user_id, 
          friend_id: friend_id }));
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
    }, [username,friend_id]);

    const sendMessage = () => {
      if (ws && input.trim()) {
        ws.send(JSON.stringify({ type: "message", user: username, text: input }));
        setInput("");
      }
    };

    return (
      <div className="chat-wrapper">
        <h2 className="chat-title">{`Phòng Chat với ${name}`}</h2>
        <button className="btn-close" onClick={onClose}>
          X
        </button>
        <div className="chat-box">
          {messages.length > 0 && (messages.map((m, i) => (
            <div
              key={i}
              className={`chat-message ${
                m.user === username ? "my-message" : "other-message"
              }`}
            >
              <strong>{m.user}:</strong> {m.text}
            </div>
          )))}
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
