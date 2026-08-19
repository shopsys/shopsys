import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ModalGallery } from 'components/Basic/ModalGallery/ModalGallery';
import { CustomerRecordRowInfo } from 'components/Pages/Customer/CustomerRecordElements';
import { TypeComplaintItemFragment } from 'graphql/requests/complaints/fragments/ComplaintItemFragment.generated';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';

type ComplaintDetailComplaintItemProps = {
    complaintItem: TypeComplaintItemFragment;
};

const GALLERY_SHOWN_ITEMS_COUNT = 5;
export const ComplaintDetailComplaintItem: FC<ComplaintDetailComplaintItemProps> = ({ complaintItem }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);

    const galleryLastShownItemIndex = GALLERY_SHOWN_ITEMS_COUNT - 1;
    const galleryAdditionalItemsCount = (complaintItem.files?.length ?? 0) - GALLERY_SHOWN_ITEMS_COUNT;
    const complaintItemUnit = complaintItem.orderItem?.unit;
    const complaintItemTotalPrice = complaintItem.orderItem?.totalPrice;
    const productLink = complaintItem.product?.isVisible ? (
        <ExtendedNextLink
            className="wrap-break-word vl:w-fit w-full text-sm text-text-default no-underline hover:text-text-hovered hover:underline"
            href={complaintItem.product.slug}
            skeletonType="product"
            type="product"
            aria-label={t('Go to product {{ productName }}', {
                ns: 'accessibility',
                productName: complaintItem.productName,
            })}
        >
            {complaintItem.productName}
        </ExtendedNextLink>
    ) : (
        complaintItem.productName
    );

    const openGallery = (initialIndex: number) => {
        if (complaintItem.files && complaintItem.files.length > 0) {
            storeCurrentFocus();

            updatePortalContent(
                <ModalGallery
                    galleryName={complaintItem.productName}
                    initialIndex={initialIndex}
                    items={complaintItem.files}
                    onCloseModal={closePortalContent}
                />,
            );
        }
    };

    return (
        <div className="flex flex-col gap-4 border-b border-b-border-default pb-5 last:border-none last:pb-0">
            <div className="flex vl:grid w-full vl:grid-cols-[minmax(0,3fr)_minmax(8rem,2fr)_1fr_2fr] flex-wrap items-center justify-between gap-3 vl:gap-5">
                <div className="flex vl:w-auto w-full min-w-0 items-center gap-5">
                    <div className="flex size-20 shrink-0">
                        <Image
                            alt={complaintItem.product?.mainImage?.name ?? complaintItem.productName}
                            className="size-full object-contain mix-blend-multiply"
                            height={80}
                            src={complaintItem.product?.mainImage?.url}
                            width={80}
                        />
                    </div>

                    <div className="wrap-break-word min-w-0 font-secondary font-semibold text-sm">{productLink}</div>
                </div>

                <span className="vl:w-auto w-full font-secondary font-semibold text-sm text-text-less">
                    {t('Code')}: {complaintItem.catnum}
                </span>

                <span className="vl:text-left text-right font-secondary font-semibold">
                    {complaintItem.quantity}
                    {complaintItemUnit && ` ${complaintItemUnit}`}
                </span>

                {complaintItemTotalPrice && isPriceVisible(complaintItemTotalPrice.priceWithVat) && (
                    <span className="text-right font-bold font-secondary">
                        {formatPrice(complaintItemTotalPrice.priceWithVat)}
                    </span>
                )}
            </div>

            <CustomerRecordRowInfo className="items-start" title={t('Description')}>
                <span className="font-secondary font-semibold">{complaintItem.description}</span>
            </CustomerRecordRowInfo>

            {!!complaintItem.files?.length && (
                <CustomerRecordRowInfo className="items-start" title={t('Attachments')}>
                    <ul className="flex w-full flex-wrap items-center gap-2">
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
                                            'flex size-16 cursor-pointer items-center justify-center rounded-xl border border-transparent bg-base-white p-2 transition-all hover:border-border-less',
                                            isWithAdditionalImages && 'relative',
                                        )}
                                        type="button"
                                        onClick={() => openGallery(imagePosition)}
                                    >
                                        <Image
                                            alt={file.anchorText || `${complaintItem.productName}-${index}`}
                                            className="size-12 object-contain mix-blend-multiply"
                                            hash={file.url.split('?')[1]}
                                            height={48}
                                            src={file.url.split('?')[0]}
                                            width={48}
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
                </CustomerRecordRowInfo>
            )}
        </div>
    );
};
