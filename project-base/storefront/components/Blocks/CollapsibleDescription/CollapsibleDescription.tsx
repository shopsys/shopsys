import { CollapsibleText } from 'components/Basic/CollapsibleText/CollapsibleText';
import { ProductsCount } from 'components/Blocks/Product/ProductsList/ProductsCount';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';

type CollapsibleDescriptionProps = {
    heading: string | null | undefined;
    description: string | null;
    currentPage: number;
    productsCount: number;
};

export const CollapsibleDescription: FC<CollapsibleDescriptionProps> = ({
    heading,
    description,
    currentPage,
    productsCount,
}) => {
    return (
        <Webline>
            {!!heading && (
                <div className="mb-5 flex flex-wrap items-baseline gap-x-3">
                    <h1 data-tid={TIDs.page_title}>{heading}</h1>

                    {/* Below vl the product count moves here from the sorting bar, so the bar fits one row. */}
                    <ProductsCount className="vl:hidden" totalCount={productsCount} />
                </div>
            )}

            {!!description && currentPage === 1 && <CollapsibleText text={description} textClassName="max-w-5xl" />}
        </Webline>
    );
};
