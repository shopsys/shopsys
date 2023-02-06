// eslint-disable-next-line no-restricted-imports
import { extendTailwindMerge } from 'tailwind-merge';
import tailwindConfig from 'tailwind.config';

const twMerge = extendTailwindMerge({
    classGroups: {
        'font-size': [{ text: Object.keys(tailwindConfig.theme?.fontSize || {}) }],
    },
    theme: {
        color: Object.keys(tailwindConfig.theme?.colors || {}),
    },
});

export default twMerge;
