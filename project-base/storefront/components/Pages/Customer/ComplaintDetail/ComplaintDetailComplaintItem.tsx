import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ModalGallery } from 'components/Basic/ModalGallery/ModalGallery';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeComplaintItemFragment } from 'graphql/requests/complaints/fragments/ComplaintItemFragment.generated';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type ComplaintDetailComplaintItemProps = {
    complaintItem: TypeComplaintItemFragment;
};

const GALLERY_SHOWN_ITEMS_COUNT = 5;
export const ComplaintDetailComplaintItem: FC<ComplaintDetailComplaintItemProps> = ({ complaintItem }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);

    const orderItem = complaintItem.orderItem as TypeOrderDetailItemFragment;

    const galleryLastShownItemIndex = GALLERY_SHOWN_ITEMS_COUNT - 1;
    const galleryAdditionalItemsCount = (complaintItem.files?.length ?? 0) - GALLERY_SHOWN_ITEMS_COUNT;
    const [selectedGalleryItemIndex, setSelectedGalleryItemIndex] = useState<number>();

    return (
        <>
            <div
                className={twJoin(
                    'flex flex-col gap-3 first:border-none first:pt-0 last:pb-0 vl:flex-row vl:items-center vl:gap-5',
                )}
            >
                <Image alt={orderItem.name} height={60} src={orderItem.product?.mainImage?.url} width={60} />
                <div className="flex w-full flex-col flex-wrap justify-between gap-3 border-b border-b-borderLess last:border-none vl:flex-row vl:items-center vl:gap-5">
                    <ExtendedNextLink className="w-fit" href={orderItem.product?.slug ?? ''} type="product">
                        {orderItem.name}
                    </ExtendedNextLink>

                    <span>
                        {t('Quantity')}: {complaintItem.quantity}
                    </span>

                    <span>
                        {t('Order number')}:{' '}
                        <ExtendedNextLink
                            type="orderDetail"
                            href={{
                                pathname: customerOrderDetailUrl,
                                query: { orderNumber: complaintItem.orderItem?.order.number },
                            }}
                        >
                            {complaintItem.orderItem?.order.number}
                        </ExtendedNextLink>
                    </span>
                </div>
            </div>

            <div className="mt-2">
                {t('Description')}: <span className="font-bold">{complaintItem.description}</span>
            </div>

            <ul className="mt-2 flex w-full items-center gap-2">
                {complaintItem.files?.map((file, index) => {
                    const isWithAdditionalImages =
                        index === galleryLastShownItemIndex && galleryAdditionalItemsCount > 0;
                    if (index > galleryLastShownItemIndex) {
                        return null;
                    }

                    const imagePosition = index > 4 ? index + 1 : index;

                    return (
                        <li
                            key={index}
                            className={twJoin(
                                'flex w-1/5 cursor-pointer items-center justify-center rounded-lg outline-1 outline-borderAccent hover:outline sm:h-16 vl:w-auto',
                                isWithAdditionalImages && 'relative',
                            )}
                            onClick={() => setSelectedGalleryItemIndex(imagePosition)}
                        >
                            <Image
                                alt={file.anchorText || `${orderItem.name}-${index}`}
                                className="aspect-square max-h-full rounded-md bg-backgroundMore object-contain p-1 mix-blend-multiply"
                                hash={file.url.split('?')[1]}
                                height={90}
                                src={file.url.split('?')[0]}
                                width={90}
                            />

                            {isWithAdditionalImages && (
                                <div className="absolute left-0 top-0 flex h-full w-full items-center justify-center rounded-lg bg-imageOverlay text-lg font-bold">
                                    +{galleryAdditionalItemsCount}
                                </div>
                            )}
                        </li>
                    );
                })}
            </ul>

            {selectedGalleryItemIndex !== undefined && complaintItem.files && complaintItem.files.length > 0 && (
                <ModalGallery
                    galleryName={orderItem.name}
                    initialIndex={selectedGalleryItemIndex}
                    items={complaintItem.files}
                    onCloseModal={() => setSelectedGalleryItemIndex(undefined)}
                />
            )}
        </>
    );
};
