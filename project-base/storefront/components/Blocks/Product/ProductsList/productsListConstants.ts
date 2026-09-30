import { twJoin } from 'tailwind-merge';

export const productListTwClass = twJoin(
    'relative grid gap-2.5 sm:gap-x-5 sm:gap-y-6',
    'grid-cols-1',
    'xs:grid-cols-2',
    'lg:grid-cols-3',
    'xl:grid-cols-4',
    'xxl:grid-cols-5',
);

export const productListViewModeListTwClass = 'relative grid grid-cols-1 gap-2';
