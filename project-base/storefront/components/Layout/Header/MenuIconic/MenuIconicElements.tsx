import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { forwardRef } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twMergeCustom } from 'utils/twMerge';

export const MenuIconicItem: FC<{ title?: string }> = ({ children, className, title }) => (
    <li className={className} title={title}>
        {children}
    </li>
);

type MenuIconicItemLinkProps = { onClick?: () => void; href?: string; title?: string; type?: PageType };

const menuIconicSubItemLinkTwClass = 'flex items-center h-14 px-5 py-3 text-sm text-text no-underline font-semibold';

export const MenuIconicSubItemLink: FC<MenuIconicItemLinkProps> = ({ children, href, onClick, type, tid }) => {
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

const menuIconicItemLinkTwClass =
    'w-10 sm:w-12 lg:w-auto flex flex-col items-center justify-center gap-1 rounded-tr-none text-[13px] leading-4 font-semibold text-linkInverted no-underline transition-colors hover:text-linkInvertedHovered hover:no-underline font-secondary';

export const MenuIconicItemLink: FC<MenuIconicItemLinkProps> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, className, tid, href, title, type, onClick }, _) => {
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
