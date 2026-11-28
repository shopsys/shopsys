import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { MouseEventHandler } from 'react';
import { twJoin } from 'tailwind-merge';
import { getSortingTabClassName } from './sortingBarUtils';

export type SortingBarItemVariant = 'tab' | 'dropdown';

type SortingBarItemProps = {
    variant: SortingBarItemVariant;
    isActive: boolean;
    href: string;
    ariaLabel: string;
    tid?: string;
    onClick: () => void;
};

export const SortingBarItem: FC<SortingBarItemProps> = ({
    children,
    variant,
    isActive,
    href,
    ariaLabel,
    tid,
    onClick,
}) => {
    const handleClick: MouseEventHandler<HTMLAnchorElement> = (event) => {
        // Keep the browser behaviour for opening the sorted list in a new tab or window.
        const isOpeningInNewContext =
            event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;

        if (isOpeningInNewContext) {
            return;
        }

        event.preventDefault();
        onClick();
    };

    const itemClassName =
        variant === 'tab'
            ? getSortingTabClassName({ isActive })
            : twJoin(
                  'flex min-h-12 w-full items-center justify-between gap-3 rounded-none px-4 py-3 text-left text-sm text-text-default no-underline hover:bg-background-more hover:text-text-default hover:no-underline',
                  isActive ? 'bg-background-accent-less font-bold' : 'font-semibold',
              );

    return (
        <a
            aria-current={isActive ? 'true' : undefined}
            aria-label={ariaLabel}
            data-tid={tid}
            href={href}
            className={itemClassName}
            onClick={handleClick}
        >
            {children}

            {variant === 'dropdown' && isActive && <CheckmarkIcon aria-hidden className="size-4 shrink-0" />}
        </a>
    );
};
