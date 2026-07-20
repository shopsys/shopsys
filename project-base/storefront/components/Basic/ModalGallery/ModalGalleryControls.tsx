import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { MediaCarouselNavigationButton } from 'components/Basic/MediaCarousel/MediaCarouselNavigationButton';
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
                className="pointer-events-auto absolute top-1/2 left-2 size-10 -translate-y-1/2 border border-border-less bg-background-default/80 text-icon shadow-sm backdrop-blur-xs transition-colors hover:bg-background-more hover:text-icon-accent focus-visible:outline-2 focus-visible:outline-border-accent focus-visible:outline-offset-2 sm:left-4 sm:size-12"
                direction="previous"
                iconClassName="size-6"
                title={t('Previous')}
                onClick={onPrevious}
            />
            <MediaCarouselNavigationButton
                className="pointer-events-auto absolute top-1/2 right-2 size-10 -translate-y-1/2 border border-border-less bg-background-default/80 text-icon shadow-sm backdrop-blur-xs transition-colors hover:bg-background-more hover:text-icon-accent focus-visible:outline-2 focus-visible:outline-border-accent focus-visible:outline-offset-2 sm:right-4 sm:size-12"
                direction="next"
                iconClassName="size-6"
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
            <button
                aria-label={t('Close')}
                className="inline-flex size-10 cursor-pointer items-center justify-center rounded-full border border-border-less bg-background-default/80 text-icon shadow-sm outline-hidden backdrop-blur-xs transition-colors hover:bg-background-more hover:text-icon-accent focus-visible:outline-2 focus-visible:outline-border-accent focus-visible:outline-offset-2 sm:size-12"
                ref={ref}
                title={t('Close')}
                type="button"
                onClick={(event) => {
                    event.stopPropagation();
                    onClose();
                }}
            >
                <CloseIcon aria-hidden="true" className="size-4" />
            </button>
        );
    },
);

ModalGalleryCloseButton.displayName = 'ModalGalleryCloseButton';
