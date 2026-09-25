import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { type CloseButtonProps, Slide, ToastContainer } from 'react-toastify';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import 'react-toastify/dist/ReactToastify.css';

export const ToastContainerWrapper = () => {
    const { t } = useTranslation();

    const closeButton = ({ closeToast }: CloseButtonProps) => (
        <IconButton
            className="ml-auto"
            Icon={CloseIcon}
            aria-hidden="true"
            shape="rounded"
            size="small"
            tabIndex={-1}
            title={t('Close')}
            variant="ghost"
            onClick={(event) => {
                event.stopPropagation();
                closeToast(true);
            }}
        />
    );

    return (
        <ToastContainer
            aria-label={t('Notifications', { ns: 'accessibility' })}
            autoClose={6000}
            closeButton={closeButton}
            hideProgressBar
            position="top-center"
            theme="colored"
            transition={Slide}
        />
    );
};
