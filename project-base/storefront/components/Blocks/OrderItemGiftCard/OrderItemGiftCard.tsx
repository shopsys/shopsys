import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { GiftIcon } from 'components/Basic/Icon/GiftIcon';
import { Image } from 'components/Basic/Image/Image';
import { SelectableCode } from 'components/Basic/SelectableCode/SelectableCode';
import { OrderItemPrice } from 'components/Blocks/OrderItemProductCard/OrderItemPrice';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { TIDs } from 'cypress/tids';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeAvailability } from 'graphql/types';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { generateProductImageAlt } from 'utils/productAltText';
import { twMergeCustom } from 'utils/twMerge';

type OrderItemGiftCardProps = {
    availability?: TypeAvailability;
    mainImage?: TypeImageFragment | null;
    fullName: string;
    categoryName?: string;
    quantity: number;
    unit: string | null;
    unitPrice: string;
    totalPrice: string;
    relatedProductNames?: string[];
    isNested?: boolean;
    isWide?: boolean;
    isQuantityInPriceRow?: boolean;
    imageTid?: string;
    productUrl?: string;
    catnum?: string | null;
};

export const OrderItemGiftCard: FC<OrderItemGiftCardProps> = ({
    mainImage,
    fullName,
    categoryName,
    quantity,
    unit,
    unitPrice,
    totalPrice,
    availability,
    relatedProductNames,
    isNested = false,
    isWide = false,
    isQuantityInPriceRow = false,
    imageTid = TIDs.order_summary_cart_item_image,
    productUrl,
    catnum,
    className,
    tid,
}) => {
    const { t } = useTranslation();
    const quantityContent = (
        <span className="whitespace-nowrap font-semibold text-sm">
            {quantity} {unit}
        </span>
    );

    return (
        <li
            data-tid={tid}
            className={twMergeCustom(
                'flex min-w-0 flex-col gap-3 rounded-xl bg-background-more p-4 font-secondary',
                isNested && 'bg-transparent p-0',
                className,
            )}
        >
            <div className="flex items-center gap-2 font-secondary font-semibold text-sm text-text-less">
                <GiftIcon aria-hidden="true" className="size-4 shrink-0 text-text-less" />
                {t('Gift with purchase')}
            </div>
            <div
                className={twMergeCustom(
                    'grid min-w-0 grid-cols-[5rem_minmax(0,1fr)_auto] items-center gap-x-2.5 gap-y-1',
                    isWide && 'md:grid-cols-[5rem_minmax(0,1fr)_auto_minmax(10rem,auto)]',
                    isQuantityInPriceRow && 'grid-cols-[5rem_minmax(0,1fr)]',
                )}
            >
                <div className="flex size-20 items-center justify-center" data-tid={imageTid}>
                    <Image
                        alt={generateProductImageAlt(fullName, categoryName, mainImage?.name)}
                        className="size-auto max-h-20 max-w-20 object-contain mix-blend-multiply"
                        height={80}
                        src={mainImage?.url}
                        width={80}
                    />
                </div>
                <div className="min-w-0">
                    {productUrl ? (
                        <ExtendedNextLink
                            href={productUrl}
                            skeletonType="product"
                            className="wrap-break-words font-semibold text-sm text-text-default no-underline hover:underline"
                        >
                            {fullName}
                        </ExtendedNextLink>
                    ) : (
                        <span className="wrap-break-words font-semibold text-sm">{fullName}</span>
                    )}
                    {availability && (
                        <ProductAvailability
                            availability={availability}
                            availableStoresCount={null}
                            className="mt-0.5"
                            displayMode="compact"
                            isInquiryType={false}
                        />
                    )}
                    {catnum && (
                        <SelectableCode
                            className="mt-1 block text-sm text-text-less"
                            label={t('Code')}
                            value={catnum}
                        />
                    )}
                    {relatedProductNames && relatedProductNames.length > 1 && (
                        <p className="mt-1 text-text-less text-xs">
                            {t('Gift for: {{ products }}', { products: relatedProductNames.join(', ') })}
                        </p>
                    )}
                </div>
                {!isQuantityInPriceRow && (
                    <div className={twMergeCustom('col-start-3 row-start-1', isWide && 'md:px-4')}>
                        {quantityContent}
                    </div>
                )}
                <div
                    className={twMergeCustom('col-span-full', isWide && 'md:col-span-1 md:col-start-4 md:row-start-1')}
                >
                    <OrderItemPrice hideFreeUnitPrice totalPrice={totalPrice} unit={unit} unitPrice={unitPrice}>
                        {isQuantityInPriceRow ? quantityContent : undefined}
                    </OrderItemPrice>
                </div>
            </div>
        </li>
    );
};
