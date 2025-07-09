import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { WatchDogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';

const ProductVariantsAvailabilityPopup = dynamic(
    () =>
        import('components/Blocks/Popup/ProductVariantsAvailabilityPopup').then(
            (component) => component.ProductVariantsAvailabilityPopup,
        ),
    {
        ssr: false,
    },
);

type ProductVariantsTableProps = {
    variants: TypeMainVariantDetailFragment['variants'];
};

export const ProductVariantsTable: FC<ProductVariantsTableProps> = ({ variants }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    if (variants.length === 0) {
        return <p>{t('Currently, it is not possible to purchase any variant of this product.')}</p>;
    }

    return (
        <Webline>
            <ul className="divide-border-default grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-1 lg:gap-0 lg:divide-y">
                {variants.map((variant, index) => (
                    <li
                        key={variant.uuid}
                        className="border-border-default mx-auto flex w-full max-w-sm flex-col items-center justify-between gap-2 border p-2 md:max-w-none lg:flex-row lg:border-0"
                        data-tid={TIDs.pages_productdetail_variant_ + variant.catalogNumber}
                    >
                        <div className="relative h-48 w-full lg:h-16 lg:w-16" data-tid={TIDs.product_detail_main_image}>
                            <Image
                                fill
                                priority
                                alt={variant.mainImage?.name || variant.fullName}
                                className="object-contain"
                                sizes="(max-width: 600px) 100vw, (max-width: 768px) 50vw, 64px"
                                src={variant.mainImage?.url}
                            />
                        </div>

                        <div className="font-secondary group-hover:text-link line-clamp-2 min-h-[2.5rem] text-center text-sm font-semibold group-hover:underline lg:line-clamp-none lg:min-h-fit lg:w-80 lg:text-left">
                            {variant.fullName}
                        </div>

                        {!variant.isSellingDenied && (
                            <ProductAvailability
                                availability={variant.availability}
                                availableStoresCount={variant.availableStoresCount}
                                isInquiryType={variant.isInquiryType}
                                className={twJoin(
                                    'min-w-40 text-center lg:text-left',
                                    variant.availability.status === TypeAvailabilityStatusEnum.InStock &&
                                        'cursor-pointer',
                                )}
                                onClick={() => {
                                    if (variant.availability.status === TypeAvailabilityStatusEnum.InStock) {
                                        updatePortalContent(
                                            <ProductVariantsAvailabilityPopup
                                                storeAvailabilities={variant.storeAvailabilities}
                                            />,
                                        );
                                    }
                                }}
                            />
                        )}

                        <div className="flex flex-col items-center justify-end gap-2.5 lg:ml-auto lg:min-w-96 lg:flex-row">
                            <ProductPrice className="lg:flex-col lg:items-end" productPrice={variant.price} />

                            <div className="flex flex-col gap-2">
                                <WatchDogButton
                                    availability={variant.availability}
                                    isInquiryType={variant.isInquiryType}
                                    productIsSellingDenied={variant.isSellingDenied}
                                    productName={variant.fullName}
                                    productUuid={variant.uuid}
                                />

                                <ProductAction
                                    isWithSpinbox
                                    buttonSize="large"
                                    gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                                    gtmProductListName={GtmProductListNameType.product_detail_variants_table}
                                    listIndex={index}
                                    product={variant}
                                    buttonVariant={
                                        (variant.uuid &&
                                            !variant.isInquiryType &&
                                            variant.availability.status === TypeAvailabilityStatusEnum.OutOfStock) ||
                                        variant.isSellingDenied
                                            ? 'inverted'
                                            : 'primary'
                                    }
                                />
                            </div>
                        </div>
                    </li>
                ))}
            </ul>
        </Webline>
    );
};
