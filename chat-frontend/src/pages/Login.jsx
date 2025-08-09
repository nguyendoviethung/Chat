import { useState } from "react";
import { useNavigate } from "react-router-dom";
import "./Login.css"; //
export default function Login() {
  const [username, setUsername] = useState("");
  const navigate = useNavigate();

  const handleLogin = () => {
    if (!username.trim()) {
      alert("Nhập tên trước khi vào chat!");
      return;
    }
    // Lưu tạm username vào localStorage
    localStorage.setItem("username", username);
    navigate("/chat");
  };

  return (
  <div className = "login-wrapper">
    <div className="login-container">
      <h2 className = "login-title">Login</h2>
      <input
        className="login-input"
        type="text"
        placeholder="Nhập tên..."
        value={username}
        onChange={(e) => setUsername(e.target.value)}
      />
      <button 
        onClick={handleLogin}
        className="login-button">
        Vào Chat
      </button>
    </div>
  </div>
  );
}
