import { TypeSimpleFlagFragment } from 'graphql/requests/flags/fragments/SimpleFlagFragment.generated';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';
import dynamic from 'next/dynamic';
import { useEffect, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';

import { ProductDetailGalleryItem } from './ProductDetailGallery/ProductDetailGallery.types';
import { ProductDetailGalleryMain } from './ProductDetailGallery/ProductDetailGalleryMain';
import { ProductDetailGalleryThumbnails } from './ProductDetailGallery/ProductDetailGalleryThumbnails';

type ProductDetailGalleryProps = {
    images: TypeImageFragment[];
    productName: string;
    flags: TypeSimpleFlagFragment[];
    videoIds?: TypeVideoTokenFragment[];
    percentageDiscount: number | null;
    categoryName?: string;
};

const DynamicModalGallery = dynamic(
    () => import('components/Basic/ModalGallery/ModalGallery').then((component) => component.ModalGallery),
    { ssr: false },
);

export const ProductDetailGallery: FC<ProductDetailGalleryProps> = ({
    flags,
    images,
    productName,
    videoIds = [],
    percentageDiscount,
    categoryName,
}) => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);
    const [selectedIndex, setSelectedIndex] = useState(0);

    const galleryItems: ProductDetailGalleryItem[] = [...images.slice(0, 1), ...videoIds, ...images.slice(1)];
    const lastItemIndex = galleryItems.length - 1;
    const hasMultipleItems = galleryItems.length > 1;

    useEffect(() => {
        if (selectedIndex > lastItemIndex) {
            setSelectedIndex(0);
        }
    }, [lastItemIndex, selectedIndex]);

    const openGallery = (initialIndex: number) => {
        if (!galleryItems.length) {
            return;
        }

        storeCurrentFocus();

        updatePortalContent(
            <DynamicModalGallery
                galleryName={productName}
                initialIndex={initialIndex}
                items={galleryItems}
                onCloseModal={closePortalContent}
            />,
        );
    };

    return (
        <div className="flex w-full min-w-0 basis-1/2 vl:basis-3/5 flex-col items-center gap-4">
            <ProductDetailGalleryMain
                categoryName={categoryName}
                flags={flags}
                galleryItems={galleryItems}
                percentageDiscount={percentageDiscount}
                productName={productName}
                selectedIndex={selectedIndex}
                onOpenGallery={openGallery}
                onSelectedIndexChange={setSelectedIndex}
            />

            {hasMultipleItems && (
                <ProductDetailGalleryThumbnails
                    galleryItems={galleryItems}
                    selectedIndex={selectedIndex}
                    onOpenGallery={openGallery}
                />
            )}
        </div>
    );
};
