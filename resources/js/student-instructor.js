/*
\resources\js\student-instructor.js
*/
document.addEventListener('DOMContentLoaded', () => {
    // Lessons toggle
    document.getElementById('lessons-trigger')?.addEventListener('click', toggleLessons);
    document.querySelector('.lessons-btn')?.addEventListener('click', (e) => {
        e.preventDefault();
        toggleLessons();
    });

    // Instruments toggle
    document.getElementById('instruments-trigger')?.addEventListener('click', toggleInstruments);
    document.querySelector('.instruments-btn')?.addEventListener('click', (e) => {
        e.preventDefault();
        toggleInstruments();
    });

    function toggleLessons() {
        document.getElementById('lessons-content').classList.toggle('active');
        document.getElementById('instruments-content').classList.remove('active');
        scrollTo('lessons-content');
    }

    function toggleInstruments() {
        document.getElementById('instruments-content').classList.toggle('active');
        document.getElementById('lessons-content').classList.remove('active');
        scrollTo('instruments-content');
    }

    function scrollTo(id) {
        const el = document.getElementById(id);
        if (el) {
            window.scrollTo({
                top: el.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    }
});