import { MouseEventHandler } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type SortingBarItemProps = { isActive: boolean; href?: string; onClick?: () => void };

export const SortingBarItem: FC<SortingBarItemProps> = ({ children, isActive, href, onClick }) => {
    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        e.preventDefault();
        onClick?.();
    };

    return (
        <a
            href={href}
            className={twMergeCustom(
                'py-4 text-right font-secondary text-xs font-bold uppercase text-link underline hover:text-linkHovered vl:relative vl:rounded-t-xl vl:bg-backgroundMore vl:px-5  vl:py-2.5 vl:text-center ',
                isActive &&
                    'font-semibold text-text no-underline vl:border vl:border-borderAccentLess vl:bg-background vl:after:absolute vl:after:bottom-[-2px] vl:after:left-0 vl:after:h-[2px] vl:after:w-full vl:after:bg-background',
            )}
            onClick={handleOnClick}
        >
            {children}
        </a>
    );
};
