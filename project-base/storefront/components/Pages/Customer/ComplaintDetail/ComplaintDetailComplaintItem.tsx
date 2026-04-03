import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ModalGallery } from 'components/Basic/ModalGallery/ModalGallery';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import { TypeComplaintItemFragment } from 'graphql/requests/complaints/fragments/ComplaintItemFragment.generated';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type ComplaintDetailComplaintItemProps = {
    complaintItem: TypeComplaintItemFragment;
    complaint: TypeComplaintDetailFragment;
};

const GALLERY_SHOWN_ITEMS_COUNT = 5;
export const ComplaintDetailComplaintItem: FC<ComplaintDetailComplaintItemProps> = ({ complaintItem, complaint }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);

    const galleryLastShownItemIndex = GALLERY_SHOWN_ITEMS_COUNT - 1;
    const galleryAdditionalItemsCount = (complaintItem.files?.length ?? 0) - GALLERY_SHOWN_ITEMS_COUNT;
    const complaintOrder = complaintItem.orderItem?.order;
    const { currentCustomerUserUuid, canViewCompanyOrders, canCreateOrder } = useAuthorization();
    const complaintOrderBelongsToCurrentCustomer = complaintOrder?.customerUser?.uuid === currentCustomerUserUuid;
    const hasAccessToOrder = canViewCompanyOrders || (canCreateOrder && complaintOrderBelongsToCurrentCustomer);

    const openGallery = (initialIndex: number) => {
        if (complaintItem.files && complaintItem.files.length > 0) {
            updatePortalContent(
                <ModalGallery
                    galleryName={complaintItem.productName}
                    initialIndex={initialIndex}
                    items={complaintItem.files}
                    onCloseModal={() => updatePortalContent(null)}
                />,
            );
        }
    };

    return (
        <>
            <div
                className={twJoin(
                    'flex vl:flex-row flex-col vl:items-center gap-3 vl:gap-5 first:border-none first:pt-0 last:pb-0',
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
                <div className="flex w-full vl:flex-row flex-col flex-wrap vl:items-center justify-between gap-3 vl:gap-5 border-b border-b-border-less last:border-none">
                    {complaintItem.product?.isVisible ? (
                        <ExtendedNextLink className="w-fit" href={complaintItem.product.slug} type="product">
                            {complaintItem.productName}
                        </ExtendedNextLink>
                    ) : (
                        complaintItem.productName
                    )}

                    <span>
                        {t('Quantity', { ns: 'accessibility' })}: {complaintItem.quantity}
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

            <div>
                {t('Description')}: <span className="font-bold">{complaintItem.description}</span>
            </div>

            {!!complaintItem.files?.length && (
                <ul className="flex w-full items-center gap-2">
                    {complaintItem.files?.map((file, index) => {
                        const isWithAdditionalImages =
                            index === galleryLastShownItemIndex && galleryAdditionalItemsCount > 0;
                        if (index > galleryLastShownItemIndex) {
                            return null;
                        }

                        const imagePosition = index > 4 ? index + 1 : index;

                        return (
                            <li key={file.url}>
                                <button
                                    aria-label={t('View complaint images in gallery', { ns: 'accessibility' })}
                                    title={t('Open gallery')}
                                    className={twJoin(
                                        'flex w-full cursor-pointer items-center justify-center rounded-lg border-0 bg-transparent outline-border-default hover:outline-1 sm:h-16',
                                        isWithAdditionalImages && 'relative',
                                    )}
                                    type="button"
                                    onClick={() => openGallery(imagePosition)}
                                >
                                    <span className="block size-full rounded-md bg-background-more p-1">
                                        <Image
                                            alt={file.anchorText || `${complaintItem.productName}-${index}`}
                                            className="aspect-square max-h-full object-contain mix-blend-multiply"
                                            hash={file.url.split('?')[1]}
                                            height={90}
                                            src={file.url.split('?')[0]}
                                            width={90}
                                        />
                                    </span>

                                    {isWithAdditionalImages && (
                                        <span className="absolute top-0 left-0 flex h-full w-full items-center justify-center rounded-lg bg-image-overlay font-bold text-lg">
                                            +{galleryAdditionalItemsCount}
                                        </span>
                                    )}
                                </button>
                            </li>
                        );
                    })}
                </ul>
            )}
        </>
    );
};
