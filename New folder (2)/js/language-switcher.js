document.addEventListener('DOMContentLoaded', function() {
    // Language switching functionality
    const langButtons = document.querySelectorAll('.btn-lang');
    const htmlEl = document.documentElement;
    const rtlStylesheet = document.getElementById('rtl-stylesheet');
    
    let translations = {};
    let currentLang = 'en';
    
    // Load translations
    async function loadTranslations(lang) {
        try {
            const response = await fetch(`lang/${lang}.json`);
            translations[lang] = await response.json();
        } catch (error) {
            console.error(`Error loading ${lang} translations:`, error);
        }
    }
    
    // Apply translations
    function applyTranslations(lang) {
        if (!translations[lang]) return;
        
        Object.keys(translations[lang]).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.placeholder = translations[lang][key];
                } else if (element.tagName === 'IMG') {
                    element.alt = translations[lang][key];
                } else {
                    // Check if the element has an icon child to preserve it
                    const icon = element.querySelector('i');
                    if (icon) {
                        // If it's a button or anchor with an icon, update text while keeping icon
                        element.innerHTML = icon.outerHTML + ' ' + translations[lang][key];
                    } else {
                        // For other elements, update innerHTML (to support <br/> tags in text)
                        element.innerHTML = translations[lang][key];
                    }
                }
            }
        });
    }
    
    // Set language
    async function setLanguage(lang) {
        if (!translations[lang]) {
            await loadTranslations(lang);
        }
        
        currentLang = lang;
        htmlEl.setAttribute('lang', lang);
        
        if (lang === 'ar') {
            htmlEl.setAttribute('dir', 'rtl');
            rtlStylesheet.removeAttribute('disabled');
        } else {
            htmlEl.setAttribute('dir', 'ltr');
            rtlStylesheet.setAttribute('disabled', 'true');
        }
        
        applyTranslations(lang);
        
        // Update active state on language buttons
        langButtons.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
        });
        
        // Save preference to localStorage
        localStorage.setItem('preferredLanguage', lang);
        
        // Update title tag
        document.title = translations[lang]['brand-name'] + ' | ' + (lang === 'en' ? 'Luxury Properties' : 'عقارات فاخرة');
    }
    
    // Initialize
    async function init() {
        // Load both translations
        await Promise.all([loadTranslations('en'), loadTranslations('ar')]);
        
        // Set initial language from localStorage or default to English
        const savedLang = localStorage.getItem('preferredLanguage') || 'en';
        setLanguage(savedLang);
        
        // Add click event listeners to language buttons
        langButtons.forEach(button => {
            button.addEventListener('click', function() {
                const selectedLang = this.getAttribute('data-lang');
                setLanguage(selectedLang);
            });
        });
    }
    
    init();
});