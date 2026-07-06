const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");
const passwordcnfirm = document.getElementById("password_confirmation");

if (togglePassword) {
    togglePassword.addEventListener("click", function () {
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);
        if(passwordcnfirm){
            passwordcnfirm.setAttribute("type", type);
        }

        // Toggle icon
        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
    });
}

(function () {


    // CAROUSEL BACKGROUND SLIDER (auto smooth)
    const slides = document.querySelectorAll('.bg-slide');
    let currentSlide = 0;
    if (slides.length > 0) {
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 5000); // 5 seconds
    }

    // Add loader to loginBtn to prevent multiple submissions
    const form = document.querySelector('form');
    const loginBtn = document.getElementById('loginBtn');
    if (form && loginBtn) {
        form.addEventListener('submit', function (e) {
            // Only show loader if the form is valid according to HTML5 validation
            if (form.checkValidity()) {
                const originalText = loginBtn.innerHTML;
                // Add fixed width/height to prevent button size jumping if desired
                loginBtn.style.minWidth = loginBtn.offsetWidth + 'px';
                
                // Show loader
                loginBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> कृपया प्रतीक्षा करें...';
                loginBtn.style.pointerEvents = 'none';
                loginBtn.style.opacity = '0.8';

                // We don't disable the button permanently because some browsers cache DOM state on back/forward navigation
                // and we want it to be enabled if validation fails and page re-renders. 
                // pointer-events: none is sufficient to prevent multiple clicks before reload.
            }
        });
    }


})();