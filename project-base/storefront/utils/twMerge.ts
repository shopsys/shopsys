import { extendTailwindMerge } from 'tailwind-merge';

export const twMergeCustom = extendTailwindMerge<'heading-level'>({
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
            // The heading classes from styles/base.css, so e.g. `h5` overrides the `h4` of `FormHeading`.
            'heading-level': ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
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
