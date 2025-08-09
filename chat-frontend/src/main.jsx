import React from "react";
import ReactDOM from "react-dom/client";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import App from "./App";
import Login from "./pages/Login";
import ChatRoom from "./pages/ChatRoom";

const router = createBrowserRouter([
  {
    path: "/", element: <App />,
    children: [
      { path: "/", element: <Login /> },
      { path: "/chat", element: <ChatRoom /> },
    ],
  },
]);

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <RouterProvider router={router} />
  </React.StrictMode>
);

