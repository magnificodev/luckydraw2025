// Lucky Draw Wheel App - Main JavaScript Logic (PHP Version)

// Constants and Configuration
// Note: Prize selection is now handled by backend, this array is kept for reference
// Order matches the actual wheel layout (clockwise from top)
const PRIZES = [
    { name: 'Tai nghe Bluetooth', image: 'tai-nghe.png' }, // Index 0
    { name: 'Bình thủy tinh', image: 'binh-thuy-tinh.png' }, // Index 1
    { name: 'Tag hành lý', image: 'tag-hanh-ly.png' }, // Index 2
    { name: 'Móc khóa', image: 'moc-khoa.png' }, // Index 3
    { name: 'Túi tote', image: 'tui-tote.png' }, // Index 4
    { name: 'Bình thủy tinh', image: 'binh-thuy-tinh.png' }, // Index 5
    { name: 'Móc khóa', image: 'moc-khoa.png' }, // Index 6
    { name: 'Bịt mắt ngủ', image: 'bit-mat-ngu.png' }, // Index 7
    { name: 'Tag hành lý', image: 'tag-hanh-ly.png' }, // Index 8
    { name: 'Túi tote', image: 'tui-tote.png' }, // Index 9
    { name: 'Ô gấp', image: 'o-gap.png' }, // Index 10
    { name: 'Mũ bảo hiểm', image: 'mu-bao-hiem.png' }, // Index 11
];

// DOM Elements
const phoneInput = document.getElementById('phoneInput');
const phoneError = document.getElementById('phoneError');
const phoneForm = document.getElementById('phoneForm');
const spinForm = document.getElementById('spinForm');
// Support both legacy structure (#wheel > img) and new <img class="wheel">
const wheel = document.getElementById('wheel');
const wheelImage = document.querySelector('.wheel') || (wheel ? wheel.querySelector('img') : null);
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
    // Prefer rotating the image itself for visible spin
    const targetEl = wheelImage || wheel;
    if (!targetEl) return;

    // Use the winning index from PHP (passed from server)
    if (window.winningIndex === undefined) {
        console.error('No winning index found from server');
        return;
    }

    const winningIndex = window.winningIndex;
    const totalSegments = window.totalSegments ?? 12; // Support 12 segments
    const degPer = 360 / totalSegments; // Degrees per segment (30°)

    // Simple calculation: winningIndex * degPer + spins * 360
    // Example: winningIndex = 5, spins = 3 → 5 * 30 + 3 * 360 = 150 + 1080 = 1230°
    const spins = Math.floor(3 + Math.random() * 4); // integer 3..6 full rotations
    const finalAngle = winningIndex * degPer + spins * 360;

    // Debug logs
    console.log('=== WHEEL SPIN DEBUG ===');
    console.log('Winning Index:', winningIndex);
    console.log('Prize:', PRIZES[winningIndex]?.name || 'Unknown');
    console.log('Total Segments:', totalSegments);
    console.log('Degrees per segment:', degPer);
    console.log('Random spins (integer):', spins);
    console.log('Base angle (winningIndex * degPer):', winningIndex * degPer);
    console.log('Spins angle (spins * 360):', (spins * 360).toFixed(2));
    console.log('Final angle:', finalAngle.toFixed(2));
    console.log('========================');

    targetEl.style.transform = `rotate(${finalAngle}deg)`;
}

// Auto spin on screen 2 removed; wheel spins only on button press
