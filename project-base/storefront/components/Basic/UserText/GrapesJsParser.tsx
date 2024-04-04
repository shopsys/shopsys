import { GrapesJsProducts } from './GrapesJsProducts';
import { UserText } from './UserText';
import { useProductsByCatnums } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { memo } from 'react';
import { GJS_PRODUCTS_SEPARATOR, parseCatnums } from 'utils/parsing/grapesJsParser';

type GrapesJsParserProps = {
    text: string;
};

export const GrapesJsParser: FC<GrapesJsParserProps> = memo(({ text }) => {
    const catnums = parseCatnums(text);
    const [{ data: allProductsResponse, fetching }] = useProductsByCatnums({ variables: { catnums } });

    const dividedParts = text.split(GJS_PRODUCTS_SEPARATOR).filter(Boolean);

    return (
        <>
            {dividedParts.map((part: string, index: number) => {
                if (part.match(/\[gjc-comp-(.*?)\]/g)) {
                    return (
                        <GrapesJsProducts
                            key={index}
                            allFetchedProducts={allProductsResponse}
                            fetching={fetching}
                            rawProductPart={part}
                        />
                    );
                }

                return <UserText key={index} isGrapesJs htmlContent={part} />;
            })}
        </>
    );
});

GrapesJsParser.displayName = 'GrapesJsParser';
