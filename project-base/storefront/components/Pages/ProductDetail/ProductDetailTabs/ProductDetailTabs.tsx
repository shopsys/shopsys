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
    () => import('./ProductDetailRelatedProductsTab').then((component) => component.ProductDetailRelatedProductsTab),
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

    const sortedIndividualParameters = parameters
        .filter((parameter) => parameter.group === null)
        .sort((a, b) => a.name.localeCompare(b.name));

    const groupedParameters = parameters
        .filter((parameter) => parameter.group !== null)
        .reduce(
            (groupedParametersAccumulator, parameter) => {
                const group = parameter.group as string;
                // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                groupedParametersAccumulator[group] = groupedParametersAccumulator[group] || [];
                groupedParametersAccumulator[group].push(parameter);
                return groupedParametersAccumulator;
            },
            {} as Record<string, TypeParameterFragment[]>,
        );

    const sortedGroupParameters = Object.entries(groupedParameters).map(([groupName, groupParameters]) => ({
        groupName,
        groupParameters: groupParameters.sort((a, b) => a.name.localeCompare(b.name)),
    }));

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

            {!!parameters.length && (
                <TabsContent headingTextMobile={t('Parameters')} isActive={selectedTab === 1}>
                    {sortedIndividualParameters.length > 0 && (
                        <div>
                            <Table className="mx-auto max-w-screen-lg border-0 p-0">
                                {sortedIndividualParameters.map((parameter) => (
                                    <Row
                                        key={parameter.uuid}
                                        className="border-none bg-tableBackground odd:bg-tableBackgroundContrast"
                                    >
                                        <Cell className="py-2 text-left text-sm font-bold uppercase leading-5">
                                            {parameter.name}
                                        </Cell>
                                        <Cell className="py-2 text-right text-sm leading-5">
                                            {parameter.values.map((value, index) =>
                                                formatParameterValue(
                                                    value.text +
                                                        (parameter.unit?.name ? ` (${parameter.unit.name})` : ''),
                                                    index,
                                                ),
                                            )}
                                        </Cell>
                                    </Row>
                                ))}
                            </Table>
                        </div>
                    )}

                    {sortedGroupParameters.map(({ groupName, groupParameters }) => (
                        <div key={groupName}>
                            <h2 className="mx-auto my-4 max-w-screen-lg text-lg font-bold">{groupName}</h2>
                            <Table className="mx-auto max-w-screen-lg border-0 p-0">
                                {groupParameters.map((parameter) => (
                                    <Row
                                        key={parameter.uuid}
                                        className="border-none bg-tableBackground odd:bg-tableBackgroundContrast"
                                    >
                                        <Cell className="py-2 text-left text-sm font-bold uppercase leading-5">
                                            {parameter.name}
                                        </Cell>
                                        <Cell className="py-2 text-right text-sm leading-5">
                                            {parameter.values.map((value, index) =>
                                                formatParameterValue(
                                                    value.text +
                                                        (parameter.unit?.name ? ` (${parameter.unit.name})` : ''),
                                                    index,
                                                ),
                                            )}
                                        </Cell>
                                    </Row>
                                ))}
                            </Table>
                        </div>
                    ))}
                </TabsContent>
            )}

            {!!relatedProducts.length && (
                <TabsContent headingTextMobile={t('Related Products')} isActive={selectedTab === 2}>
                    <ProductDetailRelatedProductsTab relatedProducts={relatedProducts} />{' '}
                </TabsContent>
            )}

            {!!files.length && (
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
