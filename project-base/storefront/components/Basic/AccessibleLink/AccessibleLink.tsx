import React from 'react';
import { twJoin } from 'tailwind-merge';

type AccessibleLinkProps = {
    title: string;
    href: string;
};

export const AccessibleLink: FC<AccessibleLinkProps> = ({ title, href, className }) => {
    return (
        <a
            href={href}
            tabIndex={0}
            className={twJoin(
                'bg-background-accent text-text-inverted font-secondary z-aboveOverlay absolute left-0 w-full p-2 text-center font-semibold no-underline',
                className,
            )}
        >
            {title}
        </a>
    );
};
