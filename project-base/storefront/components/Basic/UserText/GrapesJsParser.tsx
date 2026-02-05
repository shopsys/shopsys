import { GrapesJsProducts } from './GrapesJsProducts';
import { UserText } from './UserText';
import { useProductsByCatnums } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { GJS_PRODUCTS_SEPARATOR, parseCatnums } from 'utils/parsing/grapesJsParser';

type GrapesJsParserProps = {
    text: string;
    visibleSliderItems?: number;
};

export const GrapesJsParser: FC<GrapesJsParserProps> = ({ text, visibleSliderItems = 5 }) => {
    const catnums = parseCatnums(text);
    const [{ data: productsData, fetching: areProductsFetching }] = useProductsByCatnums({
        variables: { catnums },
    });

    const dividedParts = text.split(GJS_PRODUCTS_SEPARATOR).filter(Boolean);

    return (
        <div className="[&>*:last-child_*:last-child]:mb-0">
            {dividedParts.map((part) => {
                const partKey = `part-${part.slice(0, 50)}`;

                if (part.match(/\[gjc-comp-(.*?)\]/g)) {
                    return (
                        <GrapesJsProducts
                            key={partKey}
                            allFetchedProducts={productsData}
                            areProductsFetching={areProductsFetching}
                            rawProductPart={part}
                            visibleSliderItems={visibleSliderItems}
                        />
                    );
                }

                return <UserText key={partKey} isGrapesJs htmlContent={part} />;
            })}
        </div>
    );
};
