import { extendTailwindMerge } from 'tailwind-merge';

export const twMergeCustom = extendTailwindMerge({
    extend: {
        theme: {
            // Radius tokens from styles/theme.css, so e.g. `rounded-input` and `rounded-md` override each other.
            radius: ['button', 'checkbox', 'counter', 'flag', 'input', 'product-card', 'tag', 'toast'],
        },
        classGroups: {
            z: [
                {
                    z: [
                        'z-flag',
                        'z-above',
                        'z-menu',
                        'z-floatingAction',
                        'z-overlay',
                        'z-cart',
                        'z-modalOverlay',
                        'z-aboveOverlay',
                        'z-maximum',
                    ],
                },
            ],
        },
    },
});
