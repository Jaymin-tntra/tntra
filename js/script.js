document.addEventListener('DOMContentLoaded', function() {

    const langToggleBtn = document.getElementById('lang-toggle');
    const contactForm = document.getElementById('contact-form');
    const htmlEl = document.documentElement;

    // --- LANGUAGE TOGGLE ---

    function setLanguage(lang) {
        // Set HTML attributes
        htmlEl.setAttribute('lang', lang);
        htmlEl.setAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');

        // Toggle Bootstrap RTL stylesheet
        const rtlStylesheet = document.getElementById('bootstrap-rtl');
        rtlStylesheet.disabled = (lang !== 'ar');

        // Update all translatable elements
        document.querySelectorAll('[data-lang-en]').forEach(el => {
            const key = `lang-${lang}`;
            if (el.dataset[key]) {
                el.innerText = el.dataset[key];
            }
        });
        
        // Update language toggle button text
        langToggleBtn.innerText = lang === 'ar' ? 'EN' : 'AR';

        // Store preference
        localStorage.setItem('preferredLanguage', lang);
    }

    // Initialize language on page load
    const savedLang = localStorage.getItem('preferredLanguage') || 'en';
    setLanguage(savedLang);

    // Event listener for the toggle button
    if (langToggleBtn) {
        langToggleBtn.addEventListener('click', () => {
            const currentLang = htmlEl.getAttribute('lang');
            const newLang = currentLang === 'en' ? 'ar' : 'en';
            setLanguage(newLang);
        });
    }


    // --- CONTACT FORM SUBMISSION ---
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent actual form submission

            const formMessage = document.getElementById('form-message');
            const currentLang = htmlEl.getAttribute('lang');

            // Simple validation
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const message = document.getElementById('message').value;

            if (!name || !email || !message) {
                 const alertText = currentLang === 'ar' ? 'يرجى ملء جميع الحقول المطلوبة.' : 'Please fill out all required fields.';
                 formMessage.innerHTML = `<div class="alert alert-danger">${alertText}</div>`;
                 formMessage.classList.remove('d-none');
                 return;
            }

            // Simulate form submission
            // In a real application, you would send data to a server here (e.g., using fetch API)
            
            const successText = currentLang === 'ar' ? 'شكرا لتواصلك معنا! لقد تم استلام استفسارك بنجاح.' : 'Thank you for your message! Your enquiry has been received successfully.';
            
            formMessage.innerHTML = `<div class="alert alert-success">${successText}</div>`;
            formMessage.classList.remove('d-none');
            
            // Clear the form
            contactForm.reset();

            // Hide the message after a few seconds
            setTimeout(() => {
                formMessage.classList.add('d-none');
                formMessage.innerHTML = '';
            }, 5000);
        });
    }
});