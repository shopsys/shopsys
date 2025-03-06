'use client';

import { Popup } from 'components/Layout/Popup/Popup';
import { useTranslation } from 'components/providers/TranslationProvider';

export const ProductComparePopup: FC = () => {
    // const [productComparisonUrl] = getInternationalizedStaticUrls(['/product-comparison'], url);
    const { t } = useTranslation();

    return (
        <Popup>
            <div className="text-base lg:text-lg">{t('Comparison')}</div>

            <div className="flex flex-col">
                <p className="text-bigger font-semiBold mt-[15px] mb-5">{t('Product added to comparison.')}</p>
                {/* <ExtendedNextLink href={productComparisonUrl} type="comparison">
                    <Button>
                        <span>{t('Show products comparison')}</span>
                        <ArrowSecondaryIcon className="-rotate-90" />
                    </Button>
                </ExtendedNextLink> */}
            </div>
        </Popup>
    );
};

// .ts, .tsx, .js, .json, .graphql, .yaml, .css, .phpx
