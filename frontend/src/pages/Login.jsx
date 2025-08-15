import { useState, useRef, useEffect} from "react";
import { useNavigate } from "react-router-dom";
import "./Login.css"; 
import axios from "axios";

export default function Login() {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const navigate = useNavigate();
  const input_1 = useRef(null);
  const input_2 =  useRef(null);

  useEffect(() => {
    input_1.current.focus();
  }, []);

  const handleLogin = async () => {
  try {
    const result = await axios.post(
      "http://localhost:8080/login.php",
      {
        username: username,
        password: password,
      },
      {
        headers: {
          "Content-Type": "application/json",
        },
      }
    );

    if (result.data.status === "true") {
      localStorage.setItem("username", username);  // Lưu tên người dùng vào localStorage
      localStorage.setItem(`token_${username}`, result.data.token); // Lưu token vào localStorage với khóa duy nhất cho từng người dùng
      navigate("/home");
    } else {
      alert("Đăng nhập thất bại. Vui lòng thử lại.");
      console.log(result)
    }
  } catch (error) {
    console.error("Login error:", error);
    alert("Lỗi kết nối tới server");
  }
};

  return (
  <div className = "login-wrapper">
    <div className="login-container">
      <h2 className = "login-title">Login</h2>
      <input
        ref = {input_1}
        className="login-input"
        type="text"
        placeholder="Username"
        value={username}
        onChange={(e) => setUsername(e.target.value)}
         onKeyDown={(e) => {
         if (e.key === 'Enter') {
          input_2.current.focus();
          }
        }}
      />

      <input
        ref = {input_2}
        className="login-input"
        type="password"
        placeholder="Password"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        onKeyDown={(e) => {
        if (e.key === 'Enter') {
        handleLogin();
        }
      }}
      />

      <button
      onClick={handleLogin}
      className="login-button"
      >
      Vào Chat
      </button>

    </div>
  </div>
  );
}
