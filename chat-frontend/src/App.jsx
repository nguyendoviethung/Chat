import { Outlet } from "react-router-dom";
import "./App.css";
export default function App() {
  return (
    <div>
      <div className="title">App Chat</div>
      <Outlet /> {/* Nơi render các trang con */}
    </div>
  );
}
