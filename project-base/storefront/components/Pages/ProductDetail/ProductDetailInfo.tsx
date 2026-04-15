import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductDetailHeading, ProductDetailPrefix } from './ProductDetailElements';
import { ProductDetailUsps } from './ProductDetailUsps';

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

export const ProductDetailInfo: FC<ProductDetailContentProps> = ({
    namePrefix,
    name,
    nameSuffix,
    brand,
    catalogNumber,
    shortDescription,
    usps,
}) => {
    const { t } = useTranslation();

    return (
        <>
            <div>
                {namePrefix && <ProductDetailPrefix>{namePrefix}</ProductDetailPrefix>}

                <ProductDetailHeading>
                    {name} {nameSuffix}
                </ProductDetailHeading>
            </div>

            <div className="flex items-center gap-5 text-sm">
                {brand && (
                    <div>
                        <span>{t('Brand')}: </span>

                        <ExtendedNextLink
                            className="text-sm"
                            href={brand.slug}
                            title={t('Go to brand page')}
                            type="brand"
                            aria-label={t('Go to brand page of {{ brandName }}', {
                                ns: 'accessibility',
                                brandName: brand.name,
                            })}
                        >
                            {brand.name}
                        </ExtendedNextLink>
                    </div>
                )}

                <div>
                    {t('Code')}: {catalogNumber}
                </div>
            </div>

            {shortDescription && <div className="text-sm">{shortDescription}</div>}

            {usps && !!usps.length && <ProductDetailUsps usps={usps} />}
        </>
    );
};
