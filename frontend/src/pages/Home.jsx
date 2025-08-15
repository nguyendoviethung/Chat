import ChatRoom from "./ChatRoom";
import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";
import "./Home.css";

export default function Home() {
    const [hide, setHide] = useState(false);
    const [listFriend, setListFriend] = useState([]); // Danh sách bạn bè
    const [chatFriendID, setChatFriendID] = useState(); // ID của bạn bè để chat
    const [friendName , setFriendName] = useState(); // Tên của bạn đang chat
    const username = localStorage.getItem("username");  // Lấy tên người dùng từ localStorage
    const token = localStorage.getItem(`token_${username}`); // Lấy token từ localStorage với khóa duy nhất cho từng người dùng
    const navigate = useNavigate();

    const handleLogout = () => {
        localStorage.removeItem("username");
        localStorage.removeItem(`token_${username}`);
        navigate("/");  
    };

    useEffect(() => {
        const listFriend = async () => {
            try {
                const response = await axios.get("http://localhost:8080/list-friend.php", {
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": `Bearer ${token}` 
                    }
                });

                if (response.data.status === "true") {
                    setListFriend(response.data.friends);
                } else {
                    alert("Bạn không có bạn bè nào");
                }
            } catch (error) {
                console.error("Error fetching friends:", error);
                alert("Lỗi kết nối tới server");
            }
        };
        listFriend();
    }, [token, username]); 
    
 return (
    <div className="home-wrapper">
        <h1 className="home-title">Welcome to the Chat Application of {username}</h1>
              <button className="btn-logout" onClick={handleLogout}>
                Logout
              </button>
        {listFriend.length > 0 && (
            listFriend.map((friend, index) => (
                <div key={index} className="friend-item">
                    <span className="friend-name">{friend.full_name}</span>
                    <button className="btn-chat" onClick={() => {
                        setHide(true);
                        setChatFriendID(friend.friend_id);
                        setFriendName(friend.full_name);
                    }}>
                        Open Chat
                    </button>
                </div>
            ))
        )}  
         {hide && <ChatRoom 
                    onClose = {() => setHide(false)}
                    friend_ID = {chatFriendID} 
                    friendName = {friendName}
                  />
                    }
        </div>
        );
    }