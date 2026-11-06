import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import type { MouseEventHandler } from 'react';

const mobileBottomNavigationItemClassName =
    'flex min-h-14 w-full flex-col items-center justify-center gap-1 rounded-md px-1 text-xs text-text-inverted no-underline';

type MobileBottomNavigationLinkProps = {
    href: string;
    icon: SvgFC;
    label: string;
};

export const MobileBottomNavigationLink: FC<MobileBottomNavigationLinkProps> = ({
    children,
    href,
    icon: Icon,
    label,
}) => (
    <li className="relative">
        <ExtendedNextLink className={mobileBottomNavigationItemClassName} href={href} skeletonType="homepage">
            <Icon className="size-5" />
            {children}
            <span>{label}</span>
        </ExtendedNextLink>
    </li>
);

type MobileBottomNavigationButtonProps = {
    icon: SvgFC;
    isExpanded: boolean;
    label: string;
    ariaHaspopup?: 'dialog';
    name?: string;
    onClick: MouseEventHandler<HTMLButtonElement>;
};

export const MobileBottomNavigationButton: FC<MobileBottomNavigationButtonProps> = ({
    children,
    icon: Icon,
    isExpanded,
    label,
    ariaHaspopup,
    name,
    onClick,
}) => (
    <li>
        <button
            aria-expanded={isExpanded}
            aria-haspopup={ariaHaspopup}
            className={mobileBottomNavigationItemClassName}
            name={name}
            type="button"
            onClick={onClick}
        >
            <span className="relative flex">
                <Icon className="size-5" />
                {children}
            </span>
            <span>{label}</span>
        </button>
    </li>
);
