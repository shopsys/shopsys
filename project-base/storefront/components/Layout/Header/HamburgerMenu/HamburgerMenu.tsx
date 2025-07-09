import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import useTranslation from 'next-translate/useTranslation';
import { MouseEventHandler } from 'react';
import { twJoin } from 'tailwind-merge';

type HamburgerMenuProps = {
    onClick: MouseEventHandler<HTMLButtonElement> | undefined;
};

export const HamburgerMenu: FC<HamburgerMenuProps> = ({ onClick }) => {
    const { t } = useTranslation();

    return (
        <button
            className={twJoin('text-link-inverted-default flex cursor-pointer items-center rounded-sm bg-none')}
            tabIndex={0}
            title={t('Open menu')}
            type="button"
            onClick={onClick}
        >
            <MenuIcon className="size-6" />
        </button>
    );
};
