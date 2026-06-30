document.addEventListener('DOMContentLoaded', () => {
    // Retrieve User Session
    const storedUser = localStorage.getItem('user');
    let user = storedUser ? JSON.parse(storedUser) : null;

    if (!user) {
        window.location.href = 'auth.php';
        return;
    }

    // Populate UI with user details
    document.getElementById('profileName').textContent = `${user.first_name} ${user.last_name}`;
    document.getElementById('profileRole').textContent = user.role.toUpperCase();
    if (user.avatar) document.getElementById('profileAvatar').src = window.getAvatarPath(user.avatar, user.role);

    // Fill Form Fields
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.elements.first_name.value = user.first_name;
        profileForm.elements.last_name.value = user.last_name;
        profileForm.elements.email.value = user.email || '';
        profileForm.elements.phone.value = user.phone || '';
        if (profileForm.elements.skills) profileForm.elements.skills.value = user.skills || '';
        if (document.getElementById('input-latitude')) document.getElementById('input-latitude').value = user.latitude || '';
        if (document.getElementById('input-longitude')) document.getElementById('input-longitude').value = user.longitude || '';
    }

    // Toggle Skills Field Visibility
    const skillsContainer = document.getElementById('skillsContainer');
    if (skillsContainer) {
        if (user.role === 'worker') {
            skillsContainer.style.display = 'block';
        } else {
            skillsContainer.style.display = 'none';
        }
    }

    // Initialize Map
    loadLeafletResources(() => {
        const map = initMap('profile-location-map');
        const latInput = document.getElementById('input-latitude');
        const lngInput = document.getElementById('input-longitude');

        // Check if user already has a saved location
        if (user.latitude && user.longitude) {
            const savedLat = parseFloat(user.latitude);
            const savedLng = parseFloat(user.longitude);
            addLocationPickerMarker(map, (lat, lng) => {
                latInput.value = lat;
                lngInput.value = lng;
            }, savedLat, savedLng);
        } else {
            // Default to getting their current location
            getUserCurrentLocation((lat, lng) => {
                map.setView([lat, lng], 13);
                addLocationPickerMarker(map, (newLat, newLng) => {
                    latInput.value = newLat;
                    lngInput.value = newLng;
                }, lat, lng);
            }, () => {
                // Fallback to Dhaka if denied
                addLocationPickerMarker(map, (lat, lng) => {
                    latInput.value = lat;
                    lngInput.value = lng;
                });
            });
        }
    });

    // Handle Profile Update
    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = profileForm.querySelector('button');
        const originalText = btn.textContent;
        btn.textContent = 'Saving...';
        btn.disabled = true;

        const formData = new FormData(profileForm);
        formData.append('id', user.id);

        try {
            const result = await API.postForm('update_profile', formData);

            if (result.status === 'success') {
                // Update LocalStorage to reflect changes
                const updatedUser = { ...user, ...result.user };
                localStorage.setItem('user', JSON.stringify(updatedUser));

                // Update UI Immediately
                document.getElementById('profileName').textContent = `${updatedUser.first_name} ${updatedUser.last_name}`;
                alert('Profile updated successfully!');
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error(error);
            alert('Failed to update profile.');
        } finally {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    });

    // Handle Password Change
    const passwordForm = document.getElementById('passwordForm');
    passwordForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = passwordForm.querySelector('button');

        const current = passwordForm.elements.current_password.value;
        const newPass = passwordForm.elements.new_password.value;
        const confirm = passwordForm.elements.confirm_password.value;

        if (newPass !== confirm) {
            alert("New passwords do not match!");
            return;
        }

        const originalText = btn.textContent;
        btn.textContent = 'Updating...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('id', user.id);
        formData.append('current_password', current);
        formData.append('new_password', newPass);

        try {
            const result = await API.postForm('change_password', formData);

            if (result.status === 'success') {
                alert('Password changed successfully!');
                passwordForm.reset();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error(error);
            alert('Failed to change password.');
        } finally {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    });

    // Handle Avatar Upload
    const avatarInput = document.getElementById('avatarInput');
    avatarInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('id', user.id);
        formData.append('avatar', file);

        // Show loading state (opacity)
        const img = document.getElementById('profileAvatar');
        img.style.opacity = '0.5';

        try {
            const result = await API.postForm('update_avatar', formData);

            if (result.status === 'success') {
                // Update local storage
                user.avatar = result.avatar;
                localStorage.setItem('user', JSON.stringify(user));

                // Update UI (force refresh image)
                const newPath = window.getAvatarPath(result.avatar, user.role);
                img.src = newPath + '?t=' + new Date().getTime();

                // Update navbar avatar
                const navAvatar = document.querySelector('.navbar .avatar');
                if (navAvatar) navAvatar.src = window.getAvatarPath(result.avatar, user.role);

                alert('Profile picture updated!');
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error(error);
            alert('Failed to upload image.');
        } finally {
            img.style.opacity = '1';
        }
    });
});
