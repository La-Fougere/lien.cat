document.addEventListener("DOMContentLoaded", function () {
    const mobileMenuButton = document.querySelector(".mobile-menu");
    const wrapper = document.querySelector(".navigation .wrapper");
    let isMenuOpen = false;

    function getLogoPath() {
        const logos = [
            "cat",
            "paw",
            "kitty",
            "black-cat",
            "cool",
            "avatar",
            "paw1",
            "napping",
            "flying-kitty",
            "black-cat2",
        ];
        const randomLogo = logos[Math.floor(Math.random() * logos.length)];
        return `https://teamgeek.fr/lien/assets/images/${encodeURIComponent(randomLogo)}.png`;
    }

    function updateLogos() {
        document.querySelectorAll("#logo").forEach((logo) => {
            logo.src = getLogoPath();
        });
    }

    updateLogos();
    setInterval(updateLogos, 5000);

    mobileMenuButton.addEventListener("click", () => {
        isMenuOpen = !isMenuOpen;
        wrapper.style.display = isMenuOpen ? "flex" : "none";
        mobileMenuButton.textContent = isMenuOpen ? "Close" : "Menu";
    });
});
