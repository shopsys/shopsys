import { twMergeCustom } from 'helpers/twMerge';
import { forwardRef } from 'react';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';

export const MenuIconicItem: FC<{ title?: string }> = ({ children, className, dataTestId, title }) => (
    <li className={className} data-testid={dataTestId} title={title}>
        {children}
    </li>
);

type MenuIconicItemLinkProps = { onClick?: () => void; href?: string; title?: string };

export const MenuIconicSubItemLink: FC<MenuIconicItemLinkProps> = ({ children, href, onClick, dataTestId }) => {
    if (href) {
        return (
            <ExtendedNextLink
                className="block py-3 px-5 text-sm text-dark no-underline"
                data-testid={dataTestId}
                onClick={onClick}
                href={href}
                type="static"
            >
                {children}
            </ExtendedNextLink>
        );
    }

    return (
        <a className="block py-3 px-5 text-sm text-dark no-underline" data-testid={dataTestId} onClick={onClick}>
            {children}
        </a>
    );
};

const menuIconicItemLinkTwClass =
    'flex items-center justify-center py-4 px-3 gap-2 rounded-tr-none text-sm text-white no-underline transition-colors hover:text-white hover:no-underline';

export const MenuIconicItemLink: FC<MenuIconicItemLinkProps> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, className, dataTestId, href, title, onClick }, _) => {
        if (href) {
            return (
                <ExtendedNextLink
                    href={href}
                    type="static"
                    className={twMergeCustom(menuIconicItemLinkTwClass, className)}
                    onClick={onClick}
                    data-testid={dataTestId}
                    title={title}
                >
                    {children}
                </ExtendedNextLink>
            );
        }

        return (
            <div
                className={twMergeCustom(menuIconicItemLinkTwClass, className)}
                title={title}
                onClick={onClick}
                data-testid={dataTestId}
            >
                {children}
            </div>
        );
    },
);

MenuIconicItemLink.displayName = 'MenuIconicItemLink';
