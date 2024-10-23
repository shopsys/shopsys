import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Tabs, TabsContent, TabsList, TabsListItem } from 'components/Basic/Tabs/Tabs';
import { UserText } from 'components/Basic/UserText/UserText';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { TypeParameterFragment } from 'graphql/requests/parameters/fragments/ParameterFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useState } from 'react';

const ProductDetailRelatedProductsTab = dynamic(
    () => import('./ProductDetailRelatedProductsTab').then((component) => ({
        default: component.ProductDetailRelatedProductsTab
    })),
    {
        ssr: false,
    },
);
export type ProductDetailTabsProps = {
    description: string | null;
    parameters: TypeParameterFragment[];
    relatedProducts: TypeListedProductFragment[];
    files: TypeFileFragment[];
};

export const ProductDetailTabs: FC<ProductDetailTabsProps> = ({ description, parameters, relatedProducts, files }) => {
    const { t } = useTranslation();
    const [selectedTab, setSelectedTab] = useState(0);

    const formatParameterValue = (valueText: string, index: number) => {
        return index > 0 ? ' | ' + valueText : valueText;
    };

    return (
        <Tabs
            className="flex flex-col gap-4 lg:gap-0"
            selectedIndex={selectedTab}
            onSelect={(index) => setSelectedTab(index)}
        >
            <TabsList>
                <TabsListItem>{t('Overview')}</TabsListItem>

                {!!parameters.length && <TabsListItem>{t('Parameters')}</TabsListItem>}

                {!!relatedProducts.length && <TabsListItem>{t('Related Products')}</TabsListItem>}

                {!!files.length && <TabsListItem>{t('Files')}</TabsListItem>}
            </TabsList>

            <TabsContent headingTextMobile={t('Overview')} isActive={selectedTab === 0}>
                {description && <UserText htmlContent={description} />}
            </TabsContent>

            {parameters.length && (
                <TabsContent headingTextMobile={t('Parameters')} isActive={selectedTab === 1}>
                    <Table className="mx-auto max-w-screen-lg border-0 p-0">
                        {parameters.map((parameter) => (
                            <Row
                                key={parameter.uuid}
                                className="border-none bg-tableBackground odd:bg-tableBackgroundContrast"
                            >
                                <Cell className="py-2 text-left text-sm font-bold uppercase">{parameter.name}</Cell>

                                <Cell className="py-2 text-right text-sm">
                                    {parameter.values.map((value, index) =>
                                        formatParameterValue(
                                            value.text + (parameter.unit?.name ? ` (${parameter.unit.name})` : ''),
                                            index,
                                        ),
                                    )}
                                </Cell>
                            </Row>
                        ))}
                    </Table>
                </TabsContent>
            )}

            {relatedProducts.length && (
                <TabsContent headingTextMobile={t('Related Products')} isActive={selectedTab === 2}>
                    <ProductDetailRelatedProductsTab relatedProducts={relatedProducts} />{' '}
                </TabsContent>
            )}

            {files.length && (
                <TabsContent headingTextMobile={t('Files')} isActive={selectedTab === 3}>
                    <ul>
                        {files.map((file) => (
                            <li key={file.url}>
                                <a className="no-underline" href={file.url}>
                                    {file.anchorText}
                                    <ArrowSecondaryIcon className="ml-1 rotate-90" />
                                </a>
                            </li>
                        ))}
                    </ul>
                </TabsContent>
            )}
        </Tabs>
    );
};
