import { GrapesJsProducts } from './GrapesJsProducts';
import { UserText } from './UserText';
import { getProductsByCatnumsQuery } from 'app/_queries/getProductsByCatnumsQuery';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { Suspense } from 'react';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { GJS_PRODUCTS_SEPARATOR, parseCatnums } from 'utils/parsing/grapesJsParser';

type GrapesJsParserProps = {
    text: string;
    visibleSliderItems?: number;
};

export const GrapesJsParser = async ({ text, visibleSliderItems = 5 }: GrapesJsParserProps) => {
    const catnums = parseCatnums(text);
    await getProductsByCatnumsQuery(catnums);
    const dividedParts = text.split(GJS_PRODUCTS_SEPARATOR).filter(Boolean);

    return (
        <>
            {dividedParts.map((part, index) => {
                if (part.match(/\[gjc-comp-(.*?)\]/g)) {
                    return (
                        <Suspense
                            key={index}
                            fallback={
                                <div className="flex">
                                    {createEmptyArray(4).map((_, index) => (
                                        <SkeletonModuleProductListItem key={index} />
                                    ))}
                                </div>
                            }
                        >
                            <GrapesJsProducts
                                key={index}
                                catnums={catnums}
                                rawProductPart={part}
                                visibleSliderItems={visibleSliderItems}
                            />
                        </Suspense>
                    );
                }

                return <UserText key={index} isGrapesJs htmlContent={part} />;
            })}
        </>
    );
};
