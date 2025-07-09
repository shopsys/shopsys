import { AnimateAppearDiv } from 'components/Basic/Animations/AnimateAppearDiv';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { AnimatePresence } from 'framer-motion';
import { forwardRef } from 'react';
import { TouchEvent as ReactTouchEvent } from 'react';
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
        'flex items-center px-3 py-4 text-sm text-text-default no-underline font-semibold hover:no-underline gap-5 hover:text-text-default cursor-pointer w-full rounded-md',
        isActive && '!text-text-accent',
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
            className={twJoin(menuIconicSubItemLinkTwClass, 'outline-none')}
            data-tid={tid}
            tabIndex={0}
            onClick={onClick}
        >
            {children}
        </button>
    );
};

export const MenuIconicItemLink: FC<MenuIconicItemLinkProps> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, className, tid, href, title, type, tabIndex, onClick, onTouchEnd }, _) => {
        const menuIconicItemLinkTwClass =
            'w-10 sm:w-12 lg:w-auto flex flex-col items-center justify-center gap-1 text-xs rounded-sm font-semibold text-link-inverted-default no-underline transition-colors hover:text-link-inverted-hovered hover:no-underline font-secondary';

        if (href) {
            return (
                <ExtendedNextLink
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
            'border-background-default bg-background-more h-14 rounded-xl border',
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
};

export const MenuIconicItemUserPopover: FC<MenuIconicItemUserPopoverProps> = ({
    isHovered,
    isAuthenticated,
    children,
}) => {
    const isDesktop = useMediaMin('vl');

    if (!isDesktop) {
        return null;
    }

    const positionClasses = isAuthenticated
        ? '-right-[100%] min-w-[355px]'
        : 'right-0 max-w-[335px] lg:right-[-180px] lg:min-w-[740px] vl:min-w-[807px]';

    return (
        <AnimatePresence initial={false}>
            {isHovered && (
                <AnimateAppearDiv
                    className={twMergeCustom(
                        `z-cart pointer-events-auto absolute top-[54px] hidden origin-top`,
                        'bg-background-default vl:block rounded-xl p-5',
                        'before:absolute before:-top-2.5 before:left-0 before:h-2.5 before:w-full',
                        positionClasses,
                    )}
                >
                    {children}
                </AnimateAppearDiv>
            )}
        </AnimatePresence>
    );
};
