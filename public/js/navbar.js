function toggleMenu() {
    const navbar = document.getElementById("navbar");
    if (navbar.classList.contains("vertical-expanded")) {
        navbar.classList.remove("vertical-expanded")
    } else {
        navbar.classList.add("vertical-expanded");
    }
}