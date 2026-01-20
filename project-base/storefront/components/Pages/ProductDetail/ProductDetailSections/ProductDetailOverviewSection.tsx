import { ProductDetailSectionHeading } from './ProductDetailSectionHeading';
import { PRODUCT_DETAIL_SECTIONS_IDS } from './ProductDetailSections';
import { UserText } from 'components/Basic/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';
import { RefObject } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductDetailOverviewSectionProps = {
    description: string | null;
    sectionRef: RefObject<HTMLDivElement>;
};

export const ProductDetailOverviewSection = ({ description, sectionRef }: ProductDetailOverviewSectionProps) => {
    const { t } = useTranslation();

    return (
        <div className="scroll-mt-20" id={PRODUCT_DETAIL_SECTIONS_IDS.overview} ref={sectionRef}>
            <Webline>
                <ProductDetailSectionHeading>{t('Overview')}</ProductDetailSectionHeading>

                {description && <UserText htmlContent={description} />}
            </Webline>
        </div>
    );
};
