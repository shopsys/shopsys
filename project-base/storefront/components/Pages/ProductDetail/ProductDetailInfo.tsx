import { ProductDetailPrefix, ProductDetailHeading } from './ProductDetailElements';
import { ProductDetailUsps } from './ProductDetailUsps';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { getServerT } from 'utils/getServerTranslation';

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
    const t = await getServerT();

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
