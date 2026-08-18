import { Image } from 'components/Basic/Image/Image';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductReviewPhotosProps = {
    images: TypeImageFragment[];
    galleryName: string;
    isOnGreyBackground?: boolean;
};

const GALLERY_SHOWN_ITEMS_COUNT = 5;

const ModalGallery = dynamic(
    () => import('components/Basic/ModalGallery/ModalGallery').then((component) => component.ModalGallery),
    { ssr: false },
);

export const ProductReviewPhotos: FC<ProductReviewPhotosProps> = ({
    images,
    galleryName,
    isOnGreyBackground = false,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);

    if (images.length === 0) {
        return null;
    }

    const galleryLastShownItemIndex = GALLERY_SHOWN_ITEMS_COUNT - 1;
    const galleryAdditionalItemsCount = images.length - GALLERY_SHOWN_ITEMS_COUNT;

    const openGallery = (initialIndex: number) => {
        storeCurrentFocus();

        updatePortalContent(
            <ModalGallery
                galleryName={galleryName}
                initialIndex={initialIndex}
                items={images}
                onCloseModal={closePortalContent}
            />,
        );
    };

    return (
        <ul className="flex w-full flex-wrap items-center gap-2">
            {images.map((image, index) => {
                const isWithAdditionalImages = index === galleryLastShownItemIndex && galleryAdditionalItemsCount > 0;

                if (index > galleryLastShownItemIndex) {
                    return null;
                }

                return (
                    <li key={image.url}>
                        <button
                            aria-label={t('View review photos in gallery', { ns: 'accessibility' })}
                            title={t('Open gallery')}
                            type="button"
                            className={twJoin(
                                'flex size-16 cursor-pointer items-center justify-center rounded-xl border border-transparent p-2 transition-all hover:border-border-less',
                                isOnGreyBackground
                                    ? 'bg-background-default'
                                    : 'bg-background-more hover:bg-background-default',
                                isWithAdditionalImages && 'relative',
                            )}
                            onClick={() => openGallery(index)}
                        >
                            <Image
                                alt={image.name || `${galleryName}-${index}`}
                                className="size-12 object-contain mix-blend-multiply"
                                hash={image.url.split('?')[1]}
                                height={96}
                                src={image.url.split('?')[0]}
                                width={96}
                            />

                            {isWithAdditionalImages && (
                                <span className="absolute top-0 left-0 flex size-full items-center justify-center rounded-xl bg-image-overlay font-bold text-lg">
                                    +{galleryAdditionalItemsCount}
                                </span>
                            )}
                        </button>
                    </li>
                );
            })}
        </ul>
    );
};
