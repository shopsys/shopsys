import { LastVisitedProductsContent } from './LastVisitedProductsContent';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';
import { useCookiesStore } from 'store/useCookiesStore';

export type LastVisitedProductsProps = {
    currentProductCatnum?: string;
};

export const LastVisitedProducts: FC<LastVisitedProductsProps> = ({ currentProductCatnum }) => {
    const { t } = useTranslation();
    const lastVisitedProductsCatnums = useCookiesStore((state) => state.lastVisitedProductsCatnums);

    const lastVisitedProductsWithoutCurrentProduct = lastVisitedProductsCatnums?.filter(
        (lastVisitedProduct) => lastVisitedProduct !== currentProductCatnum,
    );

    if (!lastVisitedProductsWithoutCurrentProduct?.length) {
        return null;
    }

    return (
        <Webline className="my-6">
            <div className="h5">{t('Last visited products')}</div>
            <LastVisitedProductsContent productsCatnums={lastVisitedProductsWithoutCurrentProduct} />
        </Webline>
    );
};
