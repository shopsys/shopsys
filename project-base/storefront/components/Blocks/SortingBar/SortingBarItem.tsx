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
                'text-right vl:text-center font-secondary vl:relative py-4 vl:py-2.5 vl:px-5 uppercase text-xs font-bold underline text-link hover:text-linkHovered  vl:bg-backgroundMore vl:rounded-t-xl ',
                isActive &&
                    'font-semibold text-text no-underline vl:bg-background vl:border vl:border-borderAccentLess vl:after:w-full vl:after:h-[2px] vl:after:bg-background vl:after:absolute vl:after:bottom-[-2px] vl:after:left-0',
            )}
            onClick={handleOnClick}
        >
            {children}
        </a>
    );
};
