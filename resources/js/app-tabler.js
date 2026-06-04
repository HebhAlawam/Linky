// 1. طباعة البصمة للتأكد من التحميل
console.log("%c [Vite] App Tabler Loaded! ", "background: #222; color: #bada55");

// 2. استيراد Tabler JS (هذا الملف يحتوي داخله على منطق Bootstrap Collapse و Dropdown)
import '@tabler/core/dist/js/tabler.min.js';

// 3. استيراد التنسيقات
import '@tabler/core/dist/css/tabler.min.css';
import '@tabler/core/dist/css/tabler.rtl.min.css';
import '../css/tabler-overrides.css';


// 4. إعداد Alpine.js (إذا كنت تستخدمه)
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// 5. تعريف الدوال العالمية (Global Functions)
window.__toggleTheme = function() {
    const current = document.body.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    document.body.setAttribute('data-bs-theme', current);
    localStorage.setItem('tablerTheme', current);
};
