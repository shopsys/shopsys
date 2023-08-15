// eslint-disable-next-line no-restricted-imports
import { extendTailwindMerge } from 'tailwind-merge';
import tailwindConfig from 'tailwind.config';

export const twMergeCustom = extendTailwindMerge({
    classGroups: {
        'z-index': [
            {
                z: Object.keys(tailwindConfig.theme?.zIndex || {}),
            },
        ],
    },
    theme: {
        color: Object.keys(tailwindConfig.theme?.colors || {}),
    },
});
