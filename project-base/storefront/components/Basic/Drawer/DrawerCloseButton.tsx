import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type DrawerCloseButtonProps = {
    ariaLabel?: string;
    onClick: () => void;
    title?: string;
};

export const DrawerCloseButton: FC<DrawerCloseButtonProps> = ({ ariaLabel, onClick, title }) => {
    const { t } = useTranslation();
    const closeTitle = title ?? t('Close') ?? 'Close';

    return <IconButton Icon={CloseIcon} ariaLabel={ariaLabel} title={closeTitle} onClick={onClick} />;
};
