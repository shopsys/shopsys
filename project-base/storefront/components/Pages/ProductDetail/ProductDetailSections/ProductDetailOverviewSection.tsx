import { UserText } from 'components/Basic/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
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
            className="scroll-mt-fixed-header-with-navigation"
            data-tid={`${TIDs.product_detail_section_}${PRODUCT_DETAIL_SECTIONS_IDS.overview}`}
            id={PRODUCT_DETAIL_SECTIONS_IDS.overview}
            ref={sectionRef}
        >
            <Webline width="vl">
                <ProductDetailSectionHeading>{t('Overview')}</ProductDetailSectionHeading>

                {description && <UserText htmlContent={description} />}
            </Webline>
        </div>
    );
};
