
// Function to close the newsletter popup
function closeNewsletter() {
    document.getElementById('newsletter').classList.remove('active');
}

// Function to open the newsletter popup
function openNewsletter() {
    document.getElementById('newsletter').classList.add('active');
}

// Function to handle form submission
function submitForm(event) {
    event.preventDefault(); // Prevent form submission for demo purposes
    // Replace with your own logic for form submission or AJAX request
    alert('Form submitted successfully!');
    closeNewsletter();
}

// Attach event listener to open the newsletter popup when the page is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    openNewsletter();
});

// Image Slider
var slideImg = document.getElementById("slideImg");
var images = [
    "images/img1.png",
    "images/img2.png",
    "images/img3.png",
    "images/img4.png",
    "images/img5.png"
];
var len = images.length;
var i = 0;

function slider() {
    if (i >= len) {
        i = 0;
    }
    slideImg.src = images[i];
    i++;
    setTimeout(slider, 3000); // Change slide every 4 seconds
}
slider(); // Start the slider when the script loads

// Function to open the login form
function openLogin() {
    document.getElementById('loginForm').classList.add('active');
    document.getElementById('signUpForm').classList.remove('active');
    document.getElementById('forgotPasswordForm').classList.remove('active');
}

// Function to close the login form
function closeLogin() {
    document.getElementById('loginForm').classList.remove('active');
}

// Function to open the sign-up form
function showSignUp() {
    document.getElementById('loginForm').classList.remove('active');
    document.getElementById('signUpForm').classList.add('active');
    document.getElementById('forgotPasswordForm').classList.remove('active');
}

// Function to close the sign-up form
function closeSignUp() {
    document.getElementById('signUpForm').classList.remove('active');
}

// Function to open the forgot password form
function openForgotPassword() {
    document.getElementById('forgotPasswordForm').classList.add('active');
    document.getElementById('loginForm').classList.remove('active');
}

// Function to close the forgot password form
function closeForgotPassword() {
    document.getElementById('forgotPasswordForm').classList.remove('active');
    document.getElementById('loginForm').classList.add('active');
}

// Event listener for the login link
var loginLink = document.getElementById('loginLink');
if (loginLink) {
    loginLink.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default link behavior
        openLogin();
    });
}

// Event listeners for form close buttons
var closeLoginBtn = document.querySelector('.close-login');
if (closeLoginBtn) {
    closeLoginBtn.addEventListener('click', closeLogin);
}

var closeSignUpBtn = document.querySelector('.close-sign-up');
if (closeSignUpBtn) {
    closeSignUpBtn.addEventListener('click', closeSignUp);
}

var closeForgotPasswordBtn = document.querySelector('.close-forgot-password');
if (closeForgotPasswordBtn) {
    closeForgotPasswordBtn.addEventListener('click', closeForgotPassword);
}







function toggleReadMore(button) {
    var newsDescription = button.parentElement;
    var newsText = newsDescription.querySelector('.news-text');
    var moreText = newsDescription.querySelector('.more-text');

    if (newsDescription.classList.contains('expanded')) {
        newsDescription.classList.remove('expanded');
        button.textContent = 'Read More';
    } else {
        newsDescription.classList.add('expanded');
        button.textContent = 'Read Less';
    }
}



document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    hamburger.addEventListener('click', function() {
        this.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
});
