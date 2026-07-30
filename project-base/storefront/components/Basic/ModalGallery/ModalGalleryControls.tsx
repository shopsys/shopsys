import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { MediaCarouselNavigationButton } from 'components/Basic/MediaCarousel/MediaCarouselNavigationButton';
import { IconButton } from 'components/Forms/Button/IconButton';
import { forwardRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ModalGalleryNavigationProps = {
    onPrevious: () => void;
    onNext: () => void;
};

type ModalGalleryCloseButtonProps = {
    onClose: () => void;
};

export const ModalGalleryNavigation: FC<ModalGalleryNavigationProps> = ({ onPrevious, onNext }) => {
    const { t } = useTranslation();

    return (
        <div
            aria-label={t('Gallery navigation', { ns: 'accessibility' })}
            className="pointer-events-none absolute inset-0 z-above vl:block hidden"
            role="toolbar"
        >
            <MediaCarouselNavigationButton
                className="pointer-events-auto absolute top-1/2 left-4 -translate-y-1/2"
                direction="previous"
                size="large"
                title={t('Previous')}
                onClick={onPrevious}
            />
            <MediaCarouselNavigationButton
                className="pointer-events-auto absolute top-1/2 right-4 -translate-y-1/2"
                direction="next"
                size="large"
                title={t('Next')}
                onClick={onNext}
            />
        </div>
    );
};

export const ModalGalleryCloseButton = forwardRef<HTMLButtonElement, ModalGalleryCloseButtonProps>(
    ({ onClose }, ref) => {
        const { t } = useTranslation();

        return (
            <IconButton
                Icon={CloseIcon}
                className="sm:size-12"
                ref={ref}
                title={t('Close')}
                onClick={(event) => {
                    event.stopPropagation();
                    onClose();
                }}
            />
        );
    },
);

ModalGalleryCloseButton.displayName = 'ModalGalleryCloseButton';
