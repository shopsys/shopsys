import { ProductDetailHeading, ProductDetailPrefix } from 'app/_components/Page/ProductDetail/ProductDetailElements';
import { ProductDetailUsps } from 'app/_components/Page/ProductDetail/ProductDetailUsps';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';

type ProductDetailContentProps = {
    namePrefix: string | null;
    name: string;
    nameSuffix: string | null;
    brand?: {
        name: string;
        slug: string;
    } | null;
    catalogNumber: string;
    shortDescription?: string | null;
    usps?: string[];
};

export async function ProductDetailInfo({
    namePrefix,
    name,
    nameSuffix,
    brand,
    catalogNumber,
    shortDescription,
    usps,
}: ProductDetailContentProps) {
    const t = await getTranslation();

    return (
        <>
            <div className="flex flex-col">
                {namePrefix && <ProductDetailPrefix>{namePrefix}</ProductDetailPrefix>}

                <ProductDetailHeading>
                    {name} {nameSuffix}
                </ProductDetailHeading>

                <div className="flex items-center gap-5 text-sm">
                    {brand && (
                        <div>
                            <span>{t('Brand')}: </span>
                            <ExtendedNextLink className="text-sm" href={brand.slug} type="brand">
                                {brand.name}
                            </ExtendedNextLink>
                        </div>
                    )}

                    <div>
                        {t('Code')}: {catalogNumber}
                    </div>
                </div>
            </div>

            {shortDescription && <div className="text-sm">{shortDescription}</div>}

            {usps && !!usps.length && <ProductDetailUsps usps={usps} />}
        </>
    );
}
