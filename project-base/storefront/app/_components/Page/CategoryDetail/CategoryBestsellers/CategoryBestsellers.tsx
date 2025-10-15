import { CategoryBestsellersInitialProducts } from './CategoryBestsellersInitialProducts';
import { CategoryBestselllersRemainingProducts } from './CategoryBestselllersRemainingProducts';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';

export const NUMBER_OF_VISIBLE_ITEMS = 3;

type CategoryBestsellersProps = {
    products: TypeListedProductFragment[];
};

export const CategoryBestsellers: FC<CategoryBestsellersProps> = async ({ products }) => {
    const t = await getTranslation();

    if (!products.length) {
        return null;
    }

    const initialProducts = products.slice(0, NUMBER_OF_VISIBLE_ITEMS);
    const remainingProducts = products.slice(NUMBER_OF_VISIBLE_ITEMS);

    return (
        <div className="bg-background-more relative rounded-xl p-5">
            <h2 className="sr-only">{t('Bestsellers')}</h2>

            <div className="font-secondary mb-3 text-center text-lg font-semibold break-words">
                {t('Do not want to choose? Choose certainty')}
            </div>

            <CategoryBestsellersInitialProducts initialProducts={initialProducts} />

            <CategoryBestselllersRemainingProducts remainingProducts={remainingProducts} />
        </div>
    );
};
