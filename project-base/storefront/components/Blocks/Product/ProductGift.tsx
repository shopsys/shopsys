import { GiftIcon } from 'components/Basic/Icon/GiftIcon';
import { Image } from 'components/Basic/Image/Image';
import { TypeProductGiftFragment } from 'graphql/requests/products/fragments/ProductGiftFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { generateProductImageAlt } from 'utils/productAltText';

type ProductGiftProps = {
    gifts: TypeProductGiftFragment[];
};

export const ProductGift: FC<ProductGiftProps> = ({ gifts }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    if (!gifts.length) {
        return null;
    }

    return (
        <div className="flex min-w-0 flex-col gap-3 font-secondary">
            <p className="flex items-center gap-2 font-semibold text-sm text-text-less">
                <GiftIcon aria-hidden="true" className="size-4 shrink-0" />
                {t('Gift with purchase')}
            </p>
            {gifts.map((gift) => {
                const [mainImage] = gift.images;

                return (
                    <div
                        key={gift.uuid}
                        className="grid min-w-0 grid-cols-[4rem_minmax(0,1fr)] items-center gap-x-3 gap-y-1 sm:grid-cols-[4rem_minmax(0,1fr)_auto]"
                    >
                        <div className="row-span-2 flex size-16 items-center justify-center sm:row-span-1">
                            <Image
                                alt={generateProductImageAlt(gift.fullName, gift.mainCategory?.name, mainImage?.name)}
                                className="size-16 object-contain mix-blend-multiply"
                                height={64}
                                src={mainImage?.url}
                                width={64}
                            />
                        </div>
                        <p className="wrap-break-words col-start-2 min-w-0 self-end font-semibold leading-snug sm:self-center">
                            {gift.fullName}
                        </p>
                        {isPriceVisible(gift.giftPrice.priceWithVat) && (
                            <p className="col-start-2 self-start whitespace-nowrap font-bold text-price-default sm:col-start-3 sm:self-center">
                                {formatPrice(gift.giftPrice.priceWithVat)}
                            </p>
                        )}
                    </div>
                );
            })}
        </div>
    );
};
