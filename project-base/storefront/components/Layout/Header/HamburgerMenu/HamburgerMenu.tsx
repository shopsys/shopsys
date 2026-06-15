import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { MouseEventHandler } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type HamburgerMenuProps = {
    isOpen: boolean;
    onClick: MouseEventHandler<HTMLButtonElement> | undefined;
};

export const HamburgerMenu: FC<HamburgerMenuProps> = ({ isOpen, onClick }) => {
    const { t } = useTranslation();

    return (
        <button
            aria-expanded={isOpen}
            className={twJoin('flex cursor-pointer items-center rounded-sm bg-none text-link-inverted-default')}
            tabIndex={0}
            title={t('Open menu')}
            type="button"
            onClick={onClick}
        >
            <MenuIcon className="size-6" />
        </button>
    );
};
