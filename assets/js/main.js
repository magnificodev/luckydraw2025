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

    // Clear error message when user types (but don't show new errors)
    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            const phone = phoneInput.value.trim();
            // Only clear error if phone is valid or empty
            if (!phone || validatePhoneNumber(phone)) {
                clearError();
            }
        });
    }
}

// Phone Number Validation
function validatePhoneNumber(phone) {
    // Vietnamese phone number: 10-11 digits, starts with 0
    const phoneRegex = /^0[0-9]{9,10}$/;
    return phoneRegex.test(phone);
}

// validatePhoneInput function removed - validation only happens on submit

function showError(message) {
    if (phoneError) {
        phoneError.textContent = message;
        phoneError.classList.add('show');
    }
}

function clearError() {
    if (phoneError) {
        phoneError.textContent = '';
        phoneError.classList.remove('show');
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
        showError('Số điện thoại không đúng định dạng');
        return;
    }

    showLoading();
}

function handleSpinSubmit(e) {
    // Always prevent default to control animation timing
    e.preventDefault();

    if (isSpinning) {
        return;
    }

    isSpinning = true;

    // Animate the wheel first
    animateWheelSpin();

    // Submit after wheel animation completes (CSS is 4s)
    setTimeout(() => {
        showLoading();
        spinForm.submit();
    }, 4100);
}

// Wheel Animation for Screen 2
function animateWheelSpin() {
    if (!wheel) return;

    // Select a random prize
    const selectedPrize = PRIZES[Math.floor(Math.random() * PRIZES.length)];

    // Calculate angle to center the pointer on the selected prize
    // Each segment is 45 degrees (360/8), so center is at angle + 22.5 degrees
    // We need to rotate the wheel in the opposite direction to center the pointer
    const segmentCenter = selectedPrize.angle + 22.5;
    const targetAngle = 360 - segmentCenter;

    // Add multiple full rotations for visual effect (5-8 full rotations)
    const fullRotations = 5 + Math.random() * 3;
    const finalAngle = targetAngle + fullRotations * 360;

    wheel.style.transform = `rotate(${finalAngle}deg)`;
}

// Auto spin on screen 2 removed; wheel spins only on button press
