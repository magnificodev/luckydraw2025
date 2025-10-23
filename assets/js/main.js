// Lucky Draw Wheel App - Main JavaScript Logic (PHP Version)

// Constants and Configuration
const PRIZES = [
    { name: 'Bình thủy tinh', image: 'binh-thuy-tinh.png', angle: 0 },
    { name: 'Bịt mắt ngủ', image: 'bit-mat-ngu.png', angle: 45 },
    { name: 'Móc khóa', image: 'moc-khoa.png', angle: 90 },
    { name: 'Mũ bảo hiểm', image: 'mu-bao-hiem.png', angle: 135 },
    { name: 'Ô gấp', image: 'o-gap.png', angle: 180 },
    { name: 'Tag hành lý', image: 'tag-hanh-ly.png', angle: 225 },
    { name: 'Tai nghe Bluetooth', image: 'tai-nghe.png', angle: 270 },
    { name: 'Túi tote', image: 'tui-tote.png', angle: 315 },
];

// DOM Elements
const phoneInput = document.getElementById('phoneInput');
const phoneError = document.getElementById('phoneError');
const phoneForm = document.getElementById('phoneForm');
const spinForm = document.getElementById('spinForm');
const wheel = document.getElementById('wheel');
const loadingOverlay = document.getElementById('loadingOverlay');

// State Management
let isSpinning = false;

// Initialize App
document.addEventListener('DOMContentLoaded', function () {
    setupEventListeners();
    showErrorFromSession();
});

// Event Listeners Setup
function setupEventListeners() {
    // Phone form submission
    if (phoneForm) {
        phoneForm.addEventListener('submit', handlePhoneSubmit);
    }

    // Spin form submission
    if (spinForm) {
        spinForm.addEventListener('submit', handleSpinSubmit);
    }

    // Real-time phone validation
    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            clearError();
            validatePhoneInput();
        });
    }
}

// Phone Number Validation
function validatePhoneNumber(phone) {
    // Vietnamese phone number: 10-11 digits, starts with 0
    const phoneRegex = /^0[0-9]{9,10}$/;
    return phoneRegex.test(phone);
}

function validatePhoneInput() {
    if (!phoneInput) return true;
    const phone = phoneInput.value.trim();
    if (phone && !validatePhoneNumber(phone)) {
        showError('Số điện thoại không đúng định dạng');
        return false;
    }
    return true;
}

function showError(message) {
    if (phoneError) {
        phoneError.textContent = message;
        phoneError.style.display = 'block';
    }
}

function clearError() {
    if (phoneError) {
        phoneError.textContent = '';
        phoneError.style.display = 'none';
    }
}

// Show error from PHP session
function showErrorFromSession() {
    // This will be handled by PHP session display
}

// Loading State Management
function showLoading() {
    if (loadingOverlay) {
        loadingOverlay.classList.add('active');
    }
}

function hideLoading() {
    if (loadingOverlay) {
        loadingOverlay.classList.remove('active');
    }
}

// Form Handlers
function handlePhoneSubmit(e) {
    const phone = phoneInput.value.trim();

    // Validate phone number
    if (!phone) {
        e.preventDefault();
        showError('Vui lòng nhập số điện thoại');
        return;
    }

    if (!validatePhoneNumber(phone)) {
        e.preventDefault();
        showError(
            'Số điện thoại không đúng định dạng. Vui lòng nhập số điện thoại Việt Nam (10-11 số, bắt đầu bằng 0)'
        );
        return;
    }

    showLoading();
}

function handleSpinSubmit(e) {
    if (isSpinning) {
        e.preventDefault();
        return;
    }

    isSpinning = true;
    showLoading();

    // Add animation delay before form submission
    setTimeout(() => {
        // Let the form submit naturally
    }, 100);
}

// Wheel Animation for Screen 2
function animateWheelSpin() {
    if (!wheel) return;

    // Random angle for visual effect (not tied to actual prize)
    const randomAngle = Math.random() * 360 * 5 + Math.random() * 360;
    wheel.style.transform = `rotate(${randomAngle}deg)`;
}

// Initialize wheel animation on screen 2
document.addEventListener('DOMContentLoaded', function () {
    // Check if we're on screen 2 and animate wheel
    const screen2 = document.getElementById('screen2');
    if (screen2 && screen2.classList.contains('active')) {
        setTimeout(() => {
            animateWheelSpin();
        }, 500);
    }
});
