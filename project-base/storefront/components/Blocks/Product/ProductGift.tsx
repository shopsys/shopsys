import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeProductGiftFragment } from 'graphql/requests/products/fragments/ProductGiftFragment.generated';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductGiftProps = {
    gift: TypeProductGiftFragment;
    variant?: 'default' | 'addToCart';
};

export const ProductGift: FC<ProductGiftProps> = ({ gift, variant = 'default' }) => {
    const { t } = useTranslation();
    const [firstImage] = gift.images;
    const mainImage = gift.images.length ? firstImage : undefined;

    return (
        <div
            className={twJoin(
                'relative flex items-center rounded-xl bg-background-more',
                variant === 'addToCart' ? 'gap-2 px-5 py-4' : 'mb-5 gap-6 px-10 py-3',
            )}
        >
            <div
                className="mb-4 flex h-12 w-24 items-center justify-center md:mb-0"
                data-tid={TIDs.add_to_cart_popup_image}
            >
                <Image
                    alt={mainImage?.name || gift.name}
                    className="max-h-12 w-auto"
                    height={48}
                    src={mainImage?.url}
                    width={72}
                />
            </div>
            <div className="flex flex-1 flex-col gap-1">
                <p className="h4">{t('Gift with purchase')}</p>
                <p className="text-xs">{gift.name}</p>
            </div>
        </div>
    );
};
