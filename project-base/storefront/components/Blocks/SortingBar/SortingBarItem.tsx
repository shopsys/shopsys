import { MouseEventHandler } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type SortingBarItemProps = { isActive: boolean; href?: string; onClick?: () => void };

export const SortingBarItem: FC<SortingBarItemProps> = ({ children, isActive, href, onClick }) => {
    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        e.preventDefault();

        if (onClick) {
            onClick();
        }
    };

    return (
        <a
            href={href}
            className={twMergeCustom(
                'block border-b-2 p-3 text-center text-xs uppercase text-dark no-underline transition hover:text-dark hover:no-underline vl:py-2',
                isActive
                    ? 'pointer-events-none hidden cursor-default hover:text-dark vl:block vl:border-primary'
                    : 'border-none',
            )}
            onClick={handleOnClick}
        >
            {children}
        </a>
    );
};
