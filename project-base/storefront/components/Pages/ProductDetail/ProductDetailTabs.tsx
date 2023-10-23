import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Tabs, TabsContent, TabsList, TabsListItem } from 'components/Basic/Tabs/Tabs';
import { UserText } from 'components/Basic/UserText/UserText';
import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { ListedProductFragmentApi, ParameterFragmentApi } from 'graphql/generated';
import { GtmProductListNameType } from 'gtm/types/enums';
import useTranslation from 'next-translate/useTranslation';

type ProductDetailTabsProps = {
    description: string | null;
    parameters: ParameterFragmentApi[];
    relatedProducts: ListedProductFragmentApi[];
};

export const ProductDetailTabs: FC<ProductDetailTabsProps> = ({ description, parameters, relatedProducts }) => {
    const { t } = useTranslation();

    const formatParameterValue = (valueText: string, index: number) => {
        return index > 0 ? ' | ' + valueText : valueText;
    };

    return (
        <Tabs className="flex flex-col gap-4 lg:gap-0">
            <TabsList>
                <TabsListItem>{t('Overview')}</TabsListItem>

                {!!parameters.length && <TabsListItem>{t('Parameters')}</TabsListItem>}

                {!!relatedProducts.length && <TabsListItem>{t('Related Products')}</TabsListItem>}
            </TabsList>

            <TabsContent headingTextMobile={t('Overview')}>
                {description && <UserText htmlContent={description} />}
            </TabsContent>

            {!!parameters.length && (
                <TabsContent headingTextMobile={t('Parameters')}>
                    <Table className="border-0 p-0">
                        {parameters.map((parameter) => (
                            <Row key={parameter.uuid} className="border-t border-greyLighter first:border-t-0">
                                <Cell className="py-2 text-left text-sm font-bold uppercase leading-5">
                                    {parameter.name}
                                </Cell>

                                <Cell className="py-2 text-right text-sm leading-5">
                                    {parameter.values.map((value, index) => formatParameterValue(value.text, index))}
                                </Cell>
                            </Row>
                        ))}
                    </Table>
                </TabsContent>
            )}

            {!!relatedProducts.length && (
                <TabsContent headingTextMobile={t('Related Products')}>
                    <ProductsSlider
                        gtmProductListName={GtmProductListNameType.product_detail_related_products}
                        products={relatedProducts}
                    />
                </TabsContent>
            )}
        </Tabs>
    );
};
