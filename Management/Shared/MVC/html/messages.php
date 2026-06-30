<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - ShobKaaj</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/messages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div id="navbar-container"></div>

    <main class="main-content">
        <div class="messenger-container">


            <aside class="conversations-panel">
                <div class="panel-header">
                    <h2>Messages</h2>
                </div>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search for people and groups">
                </div>
                <div id="conversationsList" class="conversations-list">
                    <div class="loading-state">
                        <i class="fas fa-circle-notch fa-spin"></i> Loading...
                    </div>
                </div>
            </aside>


            <section class="chat-panel">
                <div id="emptyState" class="chat-empty-state">
                    <div class="empty-icon"><i class="far fa-comments"></i></div>
                    <h3>Select a conversation</h3>
                    <p>Choose someone from the left to start chatting.</p>
                </div>

                <div id="chatInterface" class="chat-interface" style="display: none;">
                    <div class="chat-header">
                        <button id="mobileBackBtn" class="mobile-back-btn btn ghost" style="display:none;" aria-label="Back to conversations">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="chat-partner-info">
                            <img src="/project-simulator-ShobKaaj/Management/Shared/MVC/images/logo.png" id="chatPartnerAvatar" class="avatar">
                            <div class="partner-details">
                                <h3 id="chatPartnerName">User Name</h3>
                                <span class="status-text">Messenger</span>
                            </div>
                        </div>
                    </div>


                    <div id="messagesList" class="messages-list">
                    </div>
                    <form id="messageForm" class="message-input-area">
                        <input type="text" id="messageInput" class="message-input" placeholder="Type a message..." autocomplete="off">
                        <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <script src="../js/utils.js"></script>
    <script src="../js/navbar.js"></script>
    <script src="../js/messages.js"></script>
</body>

</html>