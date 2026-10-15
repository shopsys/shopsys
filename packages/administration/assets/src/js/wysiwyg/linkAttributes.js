export function buildLinkAttributes({ href, title, openInNewTab }) {
    return {
        href,
        title: title || null,
        target: openInNewTab ? '_blank' : null,
        rel: openInNewTab ? 'noopener' : null,
    };
}

export function readLinkForm(linkAttributes) {
    return {
        href: linkAttributes.href || '',
        title: linkAttributes.title || '',
        openInNewTab: linkAttributes.target === '_blank',
    };
}
