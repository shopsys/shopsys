import { Icon } from 'components/Basic/Icon/Icon';
import { FC } from 'react';
import { twJoin } from 'tailwind-merge';

type BlogSignpostIconProps = { isActive: boolean };

export const BlogSignpostIcon: FC<BlogSignpostIconProps> = ({ isActive }) => (
    <Icon
        iconType="icon"
        icon="Arrow"
        className={twJoin(
            'absolute left-3 top-1/2 translate-x-1/2 -rotate-90 text-creamWhite',
            isActive ? 'text-dark' : 'text-creamWhite',
        )}
    />
);
