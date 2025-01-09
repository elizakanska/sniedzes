// script.js

let currentIndex = 0;
const thumbnails = document.querySelectorAll('.thumbnail');
const mainImage = document.getElementById('center-image');
const modal = document.getElementsByClassName('modal');
const modalTitle = document.getElementById('modal-title');
const modalDescription = document.getElementById('modal-description');

// Function to display selected image as main image and swap with thumbnail
function selectImage(thumbnail, title, description) {
    const previousMainSrc = mainImage.src;
    const previousMainAlt = mainImage.alt;
    mainImage.src = thumbnail.src;
    mainImage.alt = title;
    mainImage.onclick = () => openModal(title, description);
    thumbnail.src = previousMainSrc;
    thumbnail.alt = previousMainAlt;
}

// Function to go to the next image
function nextImage() {
    currentIndex = (currentIndex + 1) % thumbnails.length;
    const selectedThumb = thumbnails[currentIndex];
    selectImage(selectedThumb, selectedThumb.alt, `Description for ${selectedThumb.alt}`);
}

// Function to go to the previous image
function prevImage() {
    currentIndex = (currentIndex - 1 + thumbnails.length) % thumbnails.length;
    const selectedThumb = thumbnails[currentIndex];
    selectImage(selectedThumb, selectedThumb.alt, `Description for ${selectedThumb.alt}`);
}

// Function to open modal with image description
function openModal(title, description) {
    modalTitle.textContent = title;
    modalDescription.textContent = description;
    modal.style.display = 'flex';
}

function openReviewModal() {
    document.getElementById("review-modal").style.display = "flex";
}

// Function to close the modal
function closeModal() {
    modal.style.display = 'none';
}

// Function to toggle the authentication modal
function toggleAuthModal() {
    const authModal = document.getElementById('auth-modal');
    authModal.style.display = authModal.style.display === 'flex' ? 'none' : 'flex';
}

// Function to toggle between Login and Register forms
function toggleAuthMode(event) {
    if (event) event.preventDefault();
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (loginForm.style.display === 'none') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
    }
}

// Function to handle login/logout action based on user's login status
function handleAuthAction(isLoggedIn) {
    if (isLoggedIn) {
        // Perform logout by sending a POST request to auth.php
        const formData = new FormData();
        formData.append('action', 'logout');

        fetch('auth.php', {
            method: 'POST',
            body: formData
        }).then(() => {
            // Redirect to index.php after logout
            window.location.href = 'index.php';
        }).catch(error => console.error('Error logging out:', error));
    } else {
        // Show the login modal
        toggleAuthModal();
    }
}


// Close modals when clicking outside of them
window.onclick = function(event) {
    if (event.target === modal) {
        closeModal();
    }
    const authModal = document.getElementById('auth-modal');
    if (event.target === authModal) {
        authModal.style.display = 'none';
    }
    const revModal = document.getElementById('review-modal');
    if (event.target === revModal) {
        revModal.style.display = 'none';
    }
};
