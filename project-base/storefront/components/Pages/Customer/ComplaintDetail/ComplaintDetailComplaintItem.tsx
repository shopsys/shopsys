import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ModalGallery } from 'components/Basic/ModalGallery/ModalGallery';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import { TypeComplaintItemFragment } from 'graphql/requests/complaints/fragments/ComplaintItemFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type ComplaintDetailComplaintItemProps = {
    complaintItem: TypeComplaintItemFragment;
    complaint: TypeComplaintDetailFragment;
};

const GALLERY_SHOWN_ITEMS_COUNT = 5;
export const ComplaintDetailComplaintItem: FC<ComplaintDetailComplaintItemProps> = ({ complaintItem, complaint }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);

    const galleryLastShownItemIndex = GALLERY_SHOWN_ITEMS_COUNT - 1;
    const galleryAdditionalItemsCount = (complaintItem.files?.length ?? 0) - GALLERY_SHOWN_ITEMS_COUNT;
    const [selectedGalleryItemIndex, setSelectedGalleryItemIndex] = useState<number>();
    const complaintOrder = complaintItem.orderItem?.order;
    const { currentCustomerUserUuid, canViewCompanyOrders, canCreateOrder } = useAuthorization();
    const complaintOrderBelongsToCurrentCustomer = complaintOrder?.customerUser?.uuid === currentCustomerUserUuid;
    const hasAccessToOrder = canViewCompanyOrders || (canCreateOrder && complaintOrderBelongsToCurrentCustomer);

    return (
        <>
            <div
                className={twJoin(
                    'vl:flex-row vl:items-center vl:gap-5 flex flex-col gap-3 first:border-none first:pt-0 last:pb-0',
                )}
            >
                <div className="flex h-12 w-20 shrink-0">
                    <Image
                        alt={complaintItem.productName}
                        className="object-contain mix-blend-multiply"
                        height={48}
                        src={complaintItem.product?.mainImage?.url}
                        width={80}
                    />
                </div>
                <div className="border-b-border-less vl:flex-row vl:items-center vl:gap-5 flex w-full flex-col flex-wrap justify-between gap-3 border-b last:border-none">
                    {complaintItem.product?.isVisible ? (
                        <ExtendedNextLink className="w-fit" href={complaintItem.product.slug} type="product">
                            {complaintItem.productName}
                        </ExtendedNextLink>
                    ) : (
                        complaintItem.productName
                    )}

                    <span>
                        {t('Quantity')}: {complaintItem.quantity}
                    </span>

                    {complaintOrder ? (
                        <span>
                            {t('Order number')}:{' '}
                            {hasAccessToOrder ? (
                                <ExtendedNextLink
                                    type="orderDetail"
                                    href={{
                                        pathname: customerOrderDetailUrl,
                                        query: { orderNumber: complaintOrder.number },
                                    }}
                                >
                                    {complaintOrder.number}
                                </ExtendedNextLink>
                            ) : (
                                complaintOrder.number
                            )}
                        </span>
                    ) : (
                        <span>
                            {t('Order or document number')}: {complaint.manualDocumentNumber}
                        </span>
                    )}
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
                        <li key={index}>
                            <div
                                aria-label={t('View complaint images in gallery')}
                                role="button"
                                tabIndex={0}
                                title={t('Open gallery')}
                                className={twJoin(
                                    'outline-border-default vl:w-auto flex w-1/5 cursor-pointer items-center justify-center rounded-lg hover:outline-1 sm:h-16',
                                    isWithAdditionalImages && 'relative',
                                )}
                                onClick={() => setSelectedGalleryItemIndex(imagePosition)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter' || e.key === ' ') {
                                        setSelectedGalleryItemIndex(imagePosition);
                                    }
                                }}
                            >
                                <div className="bg-background-more size-full rounded-md p-1">
                                    <Image
                                        alt={file.anchorText || `${complaintItem.productName}-${index}`}
                                        className="aspect-square max-h-full object-contain mix-blend-multiply"
                                        hash={file.url.split('?')[1]}
                                        height={90}
                                        src={file.url.split('?')[0]}
                                        width={90}
                                    />
                                </div>

                                {isWithAdditionalImages && (
                                    <div className="bg-image-overlay absolute top-0 left-0 flex h-full w-full items-center justify-center rounded-lg text-lg font-bold">
                                        +{galleryAdditionalItemsCount}
                                    </div>
                                )}
                            </div>
                        </li>
                    );
                })}
            </ul>

            {selectedGalleryItemIndex !== undefined && complaintItem.files && complaintItem.files.length > 0 && (
                <ModalGallery
                    galleryName={complaintItem.productName}
                    initialIndex={selectedGalleryItemIndex}
                    items={complaintItem.files}
                    onCloseModal={() => setSelectedGalleryItemIndex(undefined)}
                />
            )}
        </>
    );
};
