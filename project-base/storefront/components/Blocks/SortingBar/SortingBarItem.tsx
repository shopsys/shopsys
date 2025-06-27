import { MouseEventHandler } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type SortingBarItemProps = { isActive: boolean; href?: string; onClick?: () => void; ariaLabel: string };

export const SortingBarItem: FC<SortingBarItemProps> = ({ children, isActive, href, onClick, ariaLabel }) => {
    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        e.preventDefault();
        onClick?.();
    };

    return (
        <a
            aria-label={ariaLabel}
            aria-selected={isActive}
            href={href}
            role="option"
            className={twMergeCustom(
                'font-secondary text-link-default hover:text-link-hovered vl:relative vl:rounded-t-xl vl:rounded-b-none vl:bg-background-more vl:px-5 vl:py-2.5 vl:text-center py-4 text-right text-xs font-bold uppercase underline',
                isActive &&
                    'text-text-default vl:border vl:border-border-less vl:bg-background-default vl:after:absolute vl:after:bottom-[-2px] vl:after:left-0 vl:after:h-[2px] vl:after:w-full vl:after:bg-background-default font-semibold no-underline',
            )}
            onClick={handleOnClick}
        >
            {children}
        </a>
    );
};
