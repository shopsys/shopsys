import { GiftBadge } from 'components/Basic/GiftBadge/GiftBadge';
import { Image } from 'components/Basic/Image/Image';
import { TypeProductGiftFragment } from 'graphql/requests/products/fragments/ProductGiftFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductGiftProps = {
    gifts: TypeProductGiftFragment[];
};

export const ProductGift: FC<ProductGiftProps> = ({ gifts }) => {
    const { t } = useTranslation();

    if (!gifts.length) {
        return null;
    }

    return (
        <div className="flex flex-col gap-2">
            {gifts.map((gift) => {
                const [mainImage] = gift.images;

                return (
                    <div key={gift.uuid} className="relative flex items-center gap-4 rounded-xl bg-background-more p-2">
                        <GiftBadge />

                        <div className="flex h-12 shrink-0 items-center justify-center">
                            <Image
                                alt={mainImage?.name || gift.name}
                                className="aspect-video h-7 object-contain object-center mix-blend-multiply"
                                height={28}
                                src={mainImage?.url}
                                width={60}
                            />
                        </div>

                        <div className="flex flex-1 flex-col">
                            <p className="h5">{t('Gift with purchase')}</p>
                            <p className="text-sm">{gift.name}</p>
                        </div>
                    </div>
                );
            })}
        </div>
    );
};
