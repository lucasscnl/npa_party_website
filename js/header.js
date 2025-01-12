document.addEventListener('DOMContentLoaded', function () {
    const headerSection = document.querySelector('header');
    const buttons = document.querySelectorAll('.burger-button');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            // Bascule entre affichage et masquage
            headerSection.style.display = (headerSection.style.display === 'none' || headerSection.style.display === '') 
                ? 'flex' 
                : 'none';
            
            button.classList.toggle('cross');
        });
    });
});


