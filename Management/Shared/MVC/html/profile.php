<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profile - ShobKaaj</title>
    <link rel="stylesheet" href="../css/base.css" />
    <link rel="stylesheet" href="../css/profile.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="../js/navbar.js"></script>
</head>

<body>
    <!-- Navbar injected by Js/navbar.js -->

    <main class="container profile-container">
        <div class="profile-header">
            <div class="profile-avatar-wrapper">
                <img src="/project-simulator-ShobKaaj/Management/Shared/MVC/images/logo.png" id="profileAvatar" class="profile-avatar" alt="Profile">
                <button class="upload-btn" onclick="document.getElementById('avatarInput').click()" title="Change Profile Picture">
                    <i class="fas fa-camera"></i>
                </button>
                <!-- Hidden File Input -->
                <input type="file" id="avatarInput" accept="image/*" style="display: none;">
            </div>
            <h1 class="profile-title" id="profileName">Loading...</h1>
            <span class="profile-role" id="profileRole">User</span>
        </div>

        <div class="profile-section">
            <h2 class="section-title">Personal Information</h2>
            <form id="profileForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="input" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="input" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Email Address</label>
                        <input type="email" name="email" class="input input-readonly" required readonly>
                    </div>
                    <div class="form-group full-width">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="input">
                    </div>
                    <div class="form-group full-width" id="skillsContainer" style="display: none;">
                        <label>Skills (Comma separated, e.g. PHP, React)</label>
                        <input type="text" name="skills" class="input" placeholder="e.g. Graphic Design, Web Development">
                    </div>
                    <div class="form-group full-width">
                        <label>Base Location</label>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">Drop a pin to set your base location. This helps connect local workers and clients.</p>
                        <div id="profile-location-map" style="height: 300px; border-radius: var(--radius); border: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 1rem;"></div>
                        <input type="hidden" name="latitude" id="input-latitude">
                        <input type="hidden" name="longitude" id="input-longitude">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn primary-action">Save Changes</button>
                </div>
            </form>
        </div>

        <div class="profile-section">
            <h2 class="section-title">Security</h2>
            <form id="passwordForm">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="input" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="input" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="input" required minlength="6">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn outline">Change Password</button>
                </div>
            </form>
        </div>
    </main>

    <script src="../js/utils.js"></script>
    <script src="../js/map-utils.js"></script>
    <script src="../js/profile.js"></script>
</body>

</html>