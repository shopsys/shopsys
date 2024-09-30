import { AnimateAppearDiv } from 'components/Basic/Animations/AnimateAppearDiv';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { AnimatePresence } from 'framer-motion';
import { forwardRef } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

export const MenuIconicItem: FC<{ title?: string }> = ({ children, className, title }) => (
    <li className={className} title={title}>
        {children}
    </li>
);

type MenuIconicItemLinkProps = { onClick?: () => void; href?: string; title?: string; type?: PageType };

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
    ({ children, className, tid, href, title, type, onClick }, _) => {
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
    const positionClasses = isAuthenticated ? '-right-[100%]' : 'right-0';

    return (
        <AnimatePresence initial={false}>
            {isHovered && (
                <AnimateAppearDiv
                    className={twMergeCustom(
                        `pointer-events-auto absolute ${positionClasses} top-[54px] z-cart hidden ${isAuthenticated ? 'min-w-[355px]' : 'max-w-[335px] lg:right-[-180px] lg:min-w-[740px] vl:min-w-[807px]'} origin-top`,
                        'rounded-xl bg-background p-5 lg:block',
                    )}
                >
                    {children}
                </AnimateAppearDiv>
            )}
        </AnimatePresence>
    );
};
