import { Content } from './Content/Content';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { BreadcrumbFragmentApi, useComparisonQueryApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';

export const ProductsComparison: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useShopsysSelector((state) => state.domain);
    const [productsComparisonUrl] = getInternationalizedStaticUrls(['/products-comparison'], url);

    const { productsComparisonUuid } = useShopsysSelector((state) => state.user);
    const [result] = useComparisonQueryApi({ variables: { comparisonUuid: productsComparisonUuid } });
    const comparedProducts = result.data?.comparison?.products ?? [];

    //TODO
    // useGtmSliderProductsListEvent(comparedProducts, GtmListNameType.compare);

    return (
        <>
            <Webline>
                <Breadcrumbs
                    breadcrumb={[
                        {
                            __typename: 'Link',
                            name: t('Product comparison'),
                            slug: productsComparisonUrl,
                        } as BreadcrumbFragmentApi,
                    ]}
                />
                {comparedProducts.length > 0 ? (
                    <Content productsCompare={comparedProducts} />
                ) : (
                    <div className="my-[75px] flex items-center">
                        <Icon iconType="icon" icon="Info" className="mr-4 h-8 w-8" />

                        <Heading type="h3" className="!mb-0">
                            {t('Comparison does not contain any products yet.')}
                        </Heading>
                    </div>
                )}
            </Webline>
        </>
    );
};
