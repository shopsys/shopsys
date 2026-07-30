import { AnimateAppearDiv } from 'components/Basic/Animations/AnimateAppearDiv';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { AnimatePresence } from 'framer-motion';
import { forwardRef, TouchEvent as ReactTouchEvent } from 'react';
import { RemoveScroll } from 'react-remove-scroll';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';

export const MenuIconicItem: FC<{ title?: string }> = ({ children, className, title }) => (
    <li className={className} title={title}>
        {children}
    </li>
);

type MenuIconicItemLinkProps = {
    href?: string;
    title?: string;
    type?: PageType;
    onClick?: () => void;
    onTouchEnd?: (e: ReactTouchEvent<HTMLButtonElement>) => void;
    isActive?: boolean;
    tabIndex?: number;
    ariaLabel?: string;
    ariaExpanded?: boolean;
    ariaHaspopup?: 'menu' | 'dialog';
};

export const MenuIconicSubItemLink: FC<MenuIconicItemLinkProps> = ({
    children,
    href,
    onClick,
    type,
    tid,
    isActive = false,
    ariaLabel,
}) => {
    const menuIconicSubItemLinkTwClass = twJoin(
        'flex w-full cursor-pointer items-center gap-5 rounded-md px-3 py-4 font-semibold text-sm text-text-default no-underline hover:text-text-default hover:no-underline',
        isActive && 'text-text-accent!',
    );

    if (href) {
        return (
            <ExtendedNextLink
                aria-label={ariaLabel}
                className={menuIconicSubItemLinkTwClass}
                href={href}
                tabIndex={0}
                tid={tid}
                type={type}
                onClick={onClick}
            >
                {children}
            </ExtendedNextLink>
        );
    }

    return (
        <button
            aria-label={ariaLabel}
            className={twJoin(menuIconicSubItemLinkTwClass, 'outline-hidden')}
            data-tid={tid}
            tabIndex={0}
            onClick={onClick}
        >
            {children}
        </button>
    );
};

export const MenuIconicItemLink: FC<MenuIconicItemLinkProps> = forwardRef(
    (
        {
            children,
            className,
            tid,
            href,
            title,
            type,
            tabIndex,
            onClick,
            onTouchEnd,
            ariaLabel,
            ariaExpanded,
            ariaHaspopup,
        },
        _,
    ) => {
        const menuIconicItemLinkTwClass =
            'w-10 sm:w-12 lg:w-auto flex flex-col items-center justify-center gap-1 text-xs rounded-sm font-semibold text-link-inverted-default no-underline transition-colors hover:text-link-inverted-hovered hover:no-underline font-secondary';

        if (href) {
            return (
                <ExtendedNextLink
                    aria-expanded={ariaExpanded}
                    aria-haspopup={ariaHaspopup}
                    aria-label={ariaLabel}
                    className={twMergeCustom(menuIconicItemLinkTwClass, className)}
                    href={href}
                    tid={tid}
                    title={title}
                    type={type}
                    onClick={onClick}
                >
                    {children}
                </ExtendedNextLink>
            );
        }

        return (
            <button
                aria-expanded={ariaExpanded}
                aria-haspopup={ariaHaspopup}
                aria-label={ariaLabel}
                className={twMergeCustom(menuIconicItemLinkTwClass, className)}
                data-tid={tid}
                tabIndex={tabIndex}
                title={title}
                type="button"
                onClick={onClick}
                onTouchEnd={onTouchEnd}
            >
                {children}
            </button>
        );
    },
);

MenuIconicItemLink.displayName = 'MenuIconicItemLink';

type MenuIconicItemUserAuthenticatedContentListItemProps = {
    isActive?: boolean;
};

export const MenuIconicItemUserAuthenticatedContentListItem: FC<
    MenuIconicItemUserAuthenticatedContentListItemProps
> = ({ children, isActive = false }) => (
    <li
        className={twMergeCustom(
            'h-14 rounded-xl border border-background-default bg-background-more',
            'hover:border-border-less hover:bg-background-default',
            isActive && 'border-border-less bg-background-default',
        )}
    >
        {children}
    </li>
);

type MenuIconicItemUserPopoverProps = {
    isHovered: boolean;
    isAuthenticated: boolean;
    topClassName?: string;
};

export const MenuIconicItemUserPopover: FC<MenuIconicItemUserPopoverProps> = ({
    isHovered,
    isAuthenticated,
    topClassName = 'top-13.5',
    children,
}) => {
    const isDesktop = useMediaMin('vl');

    if (!isDesktop) {
        return null;
    }

    const positionClasses = isAuthenticated
        ? '-right-full min-w-88.75'
        : 'right-0 max-w-83.75 lg:-right-45 lg:min-w-185 vl:min-w-201.75';

    return (
        <AnimatePresence initial={false}>
            {isHovered && (
                <RemoveScroll>
                    <AnimateAppearDiv
                        className={twMergeCustom(
                            'pointer-events-auto absolute z-cart hidden origin-top',
                            topClassName,
                            'vl:block rounded-xl bg-background-default p-5',
                            'before:absolute before:-top-2.5 before:left-0 before:h-2.5 before:w-full',
                            positionClasses,
                        )}
                    >
                        {children}
                    </AnimateAppearDiv>
                </RemoveScroll>
            )}
        </AnimatePresence>
    );
};
