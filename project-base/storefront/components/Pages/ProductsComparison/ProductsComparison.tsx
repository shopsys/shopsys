import { Content } from './Content';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { BreadcrumbFragmentApi, useComparisonQueryApi } from 'graphql/generated';
import { useGtmSliderProductListViewEvent } from 'hooks/gtm/productList/useGtmSliderProductListViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { GtmProductListNameType } from 'types/gtm/enums';

type ProductsComparisonProps = {
    breadcrumb: BreadcrumbFragmentApi[];
};

export const ProductsComparison: FC<ProductsComparisonProps> = ({ breadcrumb }) => {
    const t = useTypedTranslationFunction();

    const productsComparisonUuid = usePersistStore((s) => s.productsComparisonUuid);
    const [result] = useComparisonQueryApi({ variables: { comparisonUuid: productsComparisonUuid } });
    const comparedProducts = result.data?.comparison?.products ?? [];
    const isLoading = result.fetching;

    const content =
        comparedProducts.length > 0 ? (
            <Content productsCompare={comparedProducts} />
        ) : (
            <div className="my-[75px] flex items-center">
                <Icon iconType="icon" icon="Info" className="mr-4 w-8" />

                <Heading type="h3" className="!mb-0">
                    {t('Comparison does not contain any products yet.')}
                </Heading>
            </div>
        );

    useGtmSliderProductListViewEvent(result.data?.comparison?.products, GtmProductListNameType.product_comparison_page);

    return (
        <Webline>
            <Breadcrumbs breadcrumb={breadcrumb} />
            {isLoading ? (
                <div className="flex w-full justify-center py-10">
                    <Loader className="w-10" />
                </div>
            ) : (
                content
            )}
        </Webline>
    );
};
