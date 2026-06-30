import { UserText } from 'components/Basic/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';
import { RefObject } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductDetailSectionHeading } from './ProductDetailSectionHeading';
import { PRODUCT_DETAIL_SECTIONS_IDS } from './ProductDetailSections';

type ProductDetailOverviewSectionProps = {
    description: string | null;
    sectionRef: RefObject<HTMLDivElement | null>;
};

export const ProductDetailOverviewSection = ({ description, sectionRef }: ProductDetailOverviewSectionProps) => {
    const { t } = useTranslation();

    return (
        <div
            className="scroll-mt-[calc(var(--sticky-navigation-offset,0)+5rem)]"
            id={PRODUCT_DETAIL_SECTIONS_IDS.overview}
            ref={sectionRef}
        >
            <Webline>
                <ProductDetailSectionHeading>{t('Overview')}</ProductDetailSectionHeading>

                {description && <UserText htmlContent={description} />}
            </Webline>
        </div>
    );
};
