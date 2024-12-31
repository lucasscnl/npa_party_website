document.addEventListener('DOMContentLoaded', function () {
    const formSection = document.querySelector('.form-section');
    const buttons = document.querySelectorAll('.add-btn, .close-btn');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            // Bascule entre affichage et masquage
            formSection.style.display = (formSection.style.display === 'none' || formSection.style.display === '') 
                ? 'block' 
                : 'none';
        });
    });
});


