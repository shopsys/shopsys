import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { MouseEventHandler } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type HamburgerMenuProps = {
    isOpen: boolean;
    onClick: MouseEventHandler<HTMLButtonElement> | undefined;
};

export const HamburgerMenu: FC<HamburgerMenuProps> = ({ isOpen, onClick }) => {
    const { t } = useTranslation();

    return (
        <IconButton
            Icon={MenuIcon}
            aria-expanded={isOpen}
            className="text-link-inverted-default hover:bg-white-alpha-700 hover:text-link-inverted-hovered active:bg-white-alpha-500"
            shape="rounded"
            title={t('Open menu')}
            variant="ghost"
            onClick={onClick}
        />
    );
};
