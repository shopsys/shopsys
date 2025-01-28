'use client';

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
    onTouchEnd?: (e: ReactTouchEvent<HTMLDivElement>) => void;
};

export const MenuIconicSubItemLink: FC<MenuIconicItemLinkProps> = ({ children, href, onClick, type, tid }) => {
    const menuIconicSubItemLinkTwClass =
        'flex items-center px-3 py-4 text-sm text-text no-underline font-semibold hover:no-underline gap-5 hover:text-text';

    if (href) {
        return (
            <ExtendedNextLink
                className={menuIconicSubItemLinkTwClass}
                href={href}
                tid={tid}
                type={type}
                onClick={onClick}
            >
                {children}
            </ExtendedNextLink>
        );
    }

    return (
        <a className={menuIconicSubItemLinkTwClass} tid={tid} onClick={onClick}>
            {children}
        </a>
    );
};

export const MenuIconicItemLink: FC<MenuIconicItemLinkProps> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, className, tid, href, title, type, onClick, onTouchEnd }, _) => {
        const menuIconicItemLinkTwClass =
            'w-10 sm:w-12 lg:w-auto flex flex-col items-center justify-center gap-1 rounded-tr-none text-[13px] leading-4 font-semibold text-linkInverted no-underline transition-colors hover:text-linkInvertedHovered hover:no-underline font-secondary';

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
            <div
                className={twMergeCustom(menuIconicItemLinkTwClass, className)}
                tid={tid}
                title={title}
                onClick={onClick}
                onTouchEnd={onTouchEnd}
            >
                {children}
            </div>
        );
    },
);

MenuIconicItemLink.displayName = 'MenuIconicItemLink';

export const MenuIconicItemUserAuthenticatedContentListItem: FC = ({ children }) => (
    <li
        className={twJoin(
            'h-14 rounded-xl border border-background bg-backgroundMore',
            'hover:border-borderAccentLess hover:bg-background',
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
                        `pointer-events-auto absolute top-[54px] z-cart hidden origin-top`,
                        'rounded-xl bg-background p-5 vl:block',
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
