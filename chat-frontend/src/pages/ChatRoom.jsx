  import { useEffect, useState } from "react";
  import axios from "axios";
  import "./ChatRoom.css";

  export default function ChatRoom({onClose, friend_ID,friendName}) {
    
    const [messages, setMessages] = useState([]);
    const [input, setInput] = useState("");
    const username = localStorage.getItem("username");  // Lấy tên người dùng từ localStorage
    const token = localStorage.getItem(`token_${username}`); // Lấy token từ localStorage với khóa duy nhất cho từng người dùng
    const [ws, setWs] = useState(null);
    const [myUserId, setMyUserId] = useState(null);

  useEffect(() => {
    const messageSent = async () => {
    try {
    const result = await axios.get("http://localhost:8080/message-sent.php", {
    params : {friend_id : friend_ID},
    headers: { Authorization: `Bearer ${token}` }
    });
      if (Array.isArray(result.data)) {
        setMessages(result.data);
        console.log(result.data)
      } else {
        console.error("API không trả về mảng:", result.data);
        setMessages([]);
      }
    } catch (error) {
    console.error("Lỗi khi lấy tin nhắn:", error);
    }
  };

    if (friend_ID) {
    messageSent();
    }

      // Kết nối Websocket
    const socket = new WebSocket("ws://localhost:9000");

    socket.onopen = () => {
      console.log("Kết nối WebSocket thành công");
      // Gửi token ngay sau khi kết nối
      socket.send(JSON.stringify({
      type: "auth",
      token: token,
        }));
      };

    //Nhận tin nhắn phía sever
      socket.onmessage = (event) => {
        const msg = JSON.parse(event.data);
        if (msg.type === "auth_success") {
          setMyUserId(msg.user_id);
          return;
        }
        if (msg.type === "message") {
          const normalized = { sender_id: msg.user, content: msg.text, sent_at: msg.time };
          setMessages((prev) => Array.isArray(prev) ? [...prev, normalized] : [normalized]);
        }
      };

    // Đóng kết nối Websocket
      socket.onclose = () => {
        console.log("Mất kết nối WebSocket");
      };

      setWs(socket);
      return () => socket.close();
    }, [friend_ID, token]);

    const sendMessage = () => {
      if (ws && input.trim()) {
        ws.send(JSON.stringify({  
        type: "message",
        receiver_id: friend_ID,
        text: input}
      ));
        setInput("");
      }
    };

    return (
      <div className="chat-wrapper">
        <h2 className="chat-title">{`Phòng Chat với ${friendName}`}</h2>
        <button className="btn-close" onClick={onClose}>
          X
        </button>
        <div className="chat-box">
          {Array.isArray(messages) && messages.length > 0 && messages.map((m, i) => (
            <div
              key={i}
              className={`chat-message ${
                m.sender_id === myUserId ? "my-message" : "other-message"
              }`}
            >
              <strong>{m.sender_id === myUserId ? username : friendName}:</strong> {m.content}
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
