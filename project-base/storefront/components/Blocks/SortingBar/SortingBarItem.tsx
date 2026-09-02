import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { AriaRole, MouseEventHandler } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type SortingBarItemProps = {
    isActive: boolean;
    href?: string;
    onClick?: () => void;
    ariaLabel: string;
    role?: AriaRole;
    tid?: string;
};

export const SortingBarItem: FC<SortingBarItemProps> = ({
    children,
    isActive,
    href,
    onClick,
    ariaLabel,
    role = 'option',
    tid,
}) => {
    const isMobileMenuItem = role === 'menuitem';

    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        e.preventDefault();
        onClick?.();
    };

    return (
        <a
            aria-label={ariaLabel}
            aria-selected={role === 'option' ? isActive : undefined}
            data-tid={tid}
            href={href}
            role={role}
            className={twMergeCustom(
                isMobileMenuItem
                    ? 'flex min-h-12 w-full items-center justify-between gap-3 rounded-none px-4 py-3 text-left font-secondary font-semibold text-sm text-text-default no-underline hover:bg-background-more'
                    : 'vl:relative vl:rounded-t-xl vl:rounded-b-none vl:bg-background-more vl:px-5 py-4 vl:py-2.5 text-left vl:text-center font-bold font-secondary text-link-default text-xs uppercase underline hover:text-link-hovered',
                isActive &&
                    (isMobileMenuItem
                        ? 'bg-background-more'
                        : 'vl:border vl:border-border-less vl:bg-background-default font-semibold text-text-default no-underline vl:after:absolute vl:after:bottom-[-2px] vl:after:left-0 vl:after:h-[2px] vl:after:w-full vl:after:bg-background-default'),
            )}
            onClick={handleOnClick}
        >
            {children}

            {isMobileMenuItem && isActive && <CheckmarkIcon aria-hidden className="size-4 shrink-0" />}
        </a>
    );
};
