import { KeyboardEvent } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type AccessibleLinkProps = {
    title: string;
    href: string;
};

export const AccessibleLink: FC<AccessibleLinkProps> = ({ title, href, className }) => {
    const handleKeyDown = (event: KeyboardEvent<HTMLAnchorElement>) => {
        if (event.key === 'Enter') {
            event.preventDefault();

            const targetId = href.replace('#', '');
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                targetElement.focus();
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    };

    return (
        <a
            href={href}
            tabIndex={0}
            className={twMergeCustom(
                'absolute left-0 -translate-x-[1500rem] rounded-md focus-visible:top-0 focus-visible:translate-x-0',
                'bg-bg-orange-500 text-text-default font-secondary z-aboveOverlay w-full p-2 text-center font-semibold no-underline',
                className,
            )}
            onKeyDown={handleKeyDown}
        >
            {title}
        </a>
    );
};
