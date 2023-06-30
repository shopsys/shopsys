import { Icon } from 'components/Basic/Icon/Icon';
import { IconName } from 'components/Basic/Icon/IconsSvgMap';
import { twMergeCustom } from 'utils/twMerge';
import { forwardRef } from 'react';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';

export const MenuIconicItemIcon: FC<{ icon: IconName }> = ({ icon, className }) => (
    <Icon iconType="icon" icon={icon} className={twMergeCustom('mr-2 w-4 text-white', className)} />
);

export const MenuIconicItem: FC = ({ children, className, dataTestId }) => (
    <li className={twMergeCustom('relative mr-5 flex last:mr-0 xl:mr-8', className)} data-testid={dataTestId}>
        {children}
    </li>
);

type MenuIconicItemLinkProps = { onClick?: () => void; href?: string };

export const MenuIconicSubItemLink: FC<MenuIconicItemLinkProps> = ({ children, href, onClick, dataTestId }) => {
    const content = (
        <a className="block py-3 px-5 text-sm text-dark no-underline" data-testid={dataTestId} onClick={onClick}>
            {children}
        </a>
    );

    if (href) {
        return (
            <ExtendedNextLink href={href} passHref type="static">
                {content}
            </ExtendedNextLink>
        );
    }

    return content;
};

export const MenuIconicItemLink: FC<MenuIconicItemLinkProps> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, className, href, onClick }, _) => {
        const Tag = href ? 'a' : 'span';

        const content = (
            <Tag
                className={twMergeCustom(
                    'flex items-center justify-center rounded-tr-none text-sm text-white no-underline transition-colors hover:text-white hover:no-underline',
                    className,
                )}
                onClick={onClick}
            >
                {children}
            </Tag>
        );

        if (href) {
            return (
                <ExtendedNextLink href={href} passHref type="static">
                    {content}
                </ExtendedNextLink>
            );
        }

        return content;
    },
);

MenuIconicItemLink.displayName = 'MenuIconicItemLink';
