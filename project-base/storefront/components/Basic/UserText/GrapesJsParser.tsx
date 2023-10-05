import { useProductsByCatnumsApi } from 'graphql/generated';
import { GJS_PRODUCTS_SEPARATOR, parseCatnums } from 'helpers/parsing/grapesJsParser';
import { memo } from 'react';
import { UserText } from './UserText';
import { GrapesJsProducts } from './GrapesJsProducts';

type GrapesJsParserProps = {
    text: string;
};

export const GrapesJsParser: FC<GrapesJsParserProps> = memo(({ text }) => {
    const catnums = parseCatnums(text);
    const [{ data: allProductsResponse, fetching }] = useProductsByCatnumsApi({ variables: { catnums } });

    const dividedParts = text.split(GJS_PRODUCTS_SEPARATOR).filter(Boolean);

    return (
        <>
            {dividedParts.map((part: string, index: number) => {
                if (part.match(/\[gjc-comp-(.*?)\]/g)) {
                    return (
                        <GrapesJsProducts
                            key={index}
                            rawProductPart={part}
                            allFetchedProducts={allProductsResponse}
                            fetching={fetching}
                        />
                    );
                }

                return <UserText htmlContent={part} isGrapesJs key={index} />;
            })}
        </>
    );
});

GrapesJsParser.displayName = 'GrapesJsParser';
