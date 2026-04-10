window.addEventListener('load', () => {
    const activePinned = document.querySelector('.sidebar [data-js-nav-pinned-item] .nav-link.show');
    if (activePinned) return;

    const allActiveElements = document.querySelectorAll('.sidebar .dropdown-item.active');
    const lastActiveElement = allActiveElements[allActiveElements.length - 1] || null;
    if (lastActiveElement)
        requestAnimationFrame(() => lastActiveElement.scrollIntoView({ block: 'center', behavior: 'smooth' }));
});
