import { extendTailwindMerge } from 'tailwind-merge';

export const twMergeCustom = extendTailwindMerge({
    extend: {
        classGroups: {
            z: [
                {
                    z: ['z-flag', 'z-above', 'z-menu', 'z-overlay', 'z-cart', 'z-aboveOverlay', 'z-maximum'],
                },
            ],
        },
    },
});
