var largeur = window.innerWidth;
    console.log(largeur);
if ( largeur > 768) {
    gsap.fromTo(
        "header nav ", 
        { opacity: 0, y: -50 }, // Position initiale (au-dessus et invisible)
        { opacity: 1, y: 0, duration: 1, stagger: 0.2, ease: "power2.out" } // Position finale (normale et visible)
    );
    gsap.from(".result-winner", { opacity: 0, y: 50, duration: 1, stagger: 0.2, delay: 0.5 });
    gsap.from(".programme-category", { opacity: 0, y: 50, duration: 1, stagger: 0.2, delay: 0.5 });

    gsap.from(".result-candidat", { opacity: 0, y: 50, duration: 1, stagger: 0.2, delay: 0.5 });

    gsap.from(".candidat-img", { opacity: 0, x: -50, duration: 1, delay: 0.5, stagger: 0.2 });

    gsap.from(".candidat-score", { x: -1000, duration: 2.5, delay: 1, stagger: 0 });

    // Slider animation
    gsap.from(".slider-img img", { opacity: 0, scale: 0.8, duration: 1, delay: 0.4 });
    gsap.from(".slider-txt span", { opacity: 0, y: 30, duration: 1, delay: 1, stagger: 0.3 });

    // Cards animation
    gsap.from(".card", { opacity: 0, y: 50, duration: 0.8, stagger: 0.2, delay: 1.4 });


    // President section
    gsap.from(".president-img img", { opacity: 0, x: -10, duration: 2, delay: 2 });
    gsap.from(".president-desc", { opacity: 0, x: 50, duration: 1, delay: 2 });
} else {
    console.log(largeur);
}