// eslint-disable-next-line no-restricted-imports
import { extendTailwindMerge } from 'tailwind-merge';
import tailwindConfig from 'tailwind.config';

export const twMergeCustom = extendTailwindMerge({
    extend: {
        classGroups: {
            z: [
                {
                    z: Object.keys(tailwindConfig.theme?.zIndex || {}),
                },
            ],
        },
        theme: {
            colors: Object.keys(tailwindConfig.theme?.colors || {}),
        },
    },
});
