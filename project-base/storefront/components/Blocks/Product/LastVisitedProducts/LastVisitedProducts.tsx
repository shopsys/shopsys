import { LastVisitedProductsContent } from './LastVisitedProductsContent';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { useCookiesStore } from 'store/useCookiesStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

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
        <Webline>
            <div data-tid={TIDs.last_visited_products}>
                <h2 className="h5 mb-3">{t('Last visited products')}</h2>

                <LastVisitedProductsContent productsCatnums={lastVisitedProductsWithoutCurrentProduct} />
            </div>
        </Webline>
    );
};
