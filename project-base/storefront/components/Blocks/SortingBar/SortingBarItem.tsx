import { MouseEventHandler } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type SortingBarItemProps = { isActive: boolean; href?: string; onClick?: () => void; ariaLabel: string; tid?: string };

export const SortingBarItem: FC<SortingBarItemProps> = ({ children, isActive, href, onClick, ariaLabel, tid }) => {
    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        e.preventDefault();
        onClick?.();
    };

    return (
        <a
            aria-label={ariaLabel}
            aria-selected={isActive}
            data-tid={tid}
            href={href}
            role="option"
            className={twMergeCustom(
                'vl:relative vl:rounded-t-xl vl:rounded-b-none vl:bg-background-more vl:px-5 py-4 vl:py-2.5 vl:text-center text-right font-bold font-secondary text-link-default text-xs uppercase underline hover:text-link-hovered',
                isActive &&
                    'vl:border vl:border-border-less vl:bg-background-default font-semibold text-text-default no-underline vl:after:absolute vl:after:bottom-[-2px] vl:after:left-0 vl:after:h-[2px] vl:after:w-full vl:after:bg-background-default',
            )}
            onClick={handleOnClick}
        >
            {children}
        </a>
    );
};
