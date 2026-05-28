import type { KeyboardEvent, MouseEvent } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type AccessibleLinkProps = {
    title: string;
    href: string;
    className?: string;
};

export const AccessibleLink: FC<AccessibleLinkProps> = ({ title, href, className }) => {
    const focusTarget = () => {
        const targetId = href.replace('#', '');
        const targetElement = document.getElementById(targetId);

        if (targetElement) {
            targetElement.focus();
            targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    const handleClick = (event: MouseEvent<HTMLAnchorElement>) => {
        event.preventDefault();
        focusTarget();
    };

    const handleKeyDown = (event: KeyboardEvent<HTMLAnchorElement>) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            focusTarget();
        }
    };

    return (
        <a
            href={href}
            tabIndex={0}
            className={twMergeCustom(
                'absolute top-0 left-0 z-aboveOverlay w-full translate-x-[-200vw] rounded-md bg-background-warning p-2 text-center font-secondary font-semibold text-text-default no-underline transition-transform',
                'focus:translate-x-0 focus-visible:translate-x-0',
                className,
            )}
            onClick={handleClick}
            onKeyDown={handleKeyDown}
        >
            {title}
        </a>
    );
};
