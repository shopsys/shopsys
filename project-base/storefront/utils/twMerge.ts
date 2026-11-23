import { extendTailwindMerge } from 'tailwind-merge';

export const twMergeCustom = extendTailwindMerge({
    extend: {
        theme: {
            // Font weight roles from styles/theme.css; without them `font-heading` would be merged as a font family and drop `font-default`.
            'font-weight': ['heading', 'action', 'ui', 'product-name', 'price'],
            // Radius tokens from styles/theme.css, so e.g. `rounded-card` and `rounded-md` override each other.
            radius: [
                'detail',
                'control',
                'card',
                'surface',
                'button',
                'checkbox',
                'counter',
                'flag',
                'input',
                'product-card',
                'tag',
                'toast',
            ],
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
