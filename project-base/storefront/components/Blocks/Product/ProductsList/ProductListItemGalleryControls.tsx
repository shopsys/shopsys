import { MediaCarouselNavigationButton } from 'components/Basic/MediaCarousel/MediaCarouselNavigationButton';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductListItemGalleryControlsProps = {
    imageHeight: number;
    onNext: () => void;
    onPrepareGallery: () => void;
    onPrevious: () => void;
};

export const ProductListItemGalleryControls: FC<ProductListItemGalleryControlsProps> = ({
    imageHeight,
    onNext,
    onPrepareGallery,
    onPrevious,
}) => {
    const { t } = useTranslation();

    return (
        <div
            className="pointer-events-none absolute top-10 right-2.5 left-2.5 z-above vl:flex hidden items-center justify-between opacity-0 transition-opacity duration-200 vl:group-focus-within:opacity-100 vl:group-hover:opacity-100 motion-reduce:transition-none"
            style={{ height: imageHeight }}
        >
            <MediaCarouselNavigationButton
                className="pointer-events-auto size-6 border border-border-less bg-background-default/90 text-icon-default shadow-sm backdrop-blur-xs transition hover:text-icon-accent"
                direction="previous"
                iconClassName="size-4"
                title={t('Previous')}
                onClick={onPrevious}
                onFocus={onPrepareGallery}
            />
            <MediaCarouselNavigationButton
                className="pointer-events-auto size-6 border border-border-less bg-background-default/90 text-icon-default shadow-sm backdrop-blur-xs transition hover:text-icon-accent"
                direction="next"
                iconClassName="size-4"
                title={t('Next')}
                onClick={onNext}
                onFocus={onPrepareGallery}
            />
        </div>
    );
};
