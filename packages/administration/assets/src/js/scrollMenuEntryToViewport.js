window.addEventListener('load', () => {
    const allActiveElements = document.querySelectorAll('.sidebar .dropdown-item.active');
    const lastActiveElement = allActiveElements[allActiveElements.length - 1] || null;
    if (lastActiveElement)
        requestAnimationFrame(() => lastActiveElement.scrollIntoView({ block: 'center', behavior: 'smooth' }));
});
