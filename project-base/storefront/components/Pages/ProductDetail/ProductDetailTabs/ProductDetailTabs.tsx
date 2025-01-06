import { DownloadIcon } from 'components/Basic/Icon/DownloadIcon';
import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Tabs, TabsContent, TabsList, TabsListItem } from 'components/Basic/Tabs/Tabs';
import { UserText } from 'components/Basic/UserText/UserText';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { TypeParameterFragment } from 'graphql/requests/parameters/fragments/ParameterFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { Fragment, useState } from 'react';
import { useMediaMin } from 'utils/ui/useMediaMin';

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
    const [skipInitialAnimation, setSkipInitialAnimation] = useState(true);
    const isLg = useMediaMin('lg');

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

    let tabIndex = 0;
    const parametersTabIndex = parameters.length ? ++tabIndex : -1;
    const relatedProductsTabIndex = relatedProducts.length ? ++tabIndex : -1;
    const filesTabIndex = files.length ? ++tabIndex : -1;

    return (
        <Tabs
            className="flex flex-col gap-4 lg:gap-0"
            selectedIndex={selectedTab}
            onSelect={(index) => {
                setSkipInitialAnimation(false);
                setSelectedTab(index);
            }}
        >
            <TabsList>
                <TabsListItem>{t('Overview')}</TabsListItem>

                {!!parameters.length && <TabsListItem>{t('Parameters')}</TabsListItem>}

                {!!relatedProducts.length && <TabsListItem>{t('Related Products')}</TabsListItem>}

                {!!files.length && <TabsListItem>{t('Files')}</TabsListItem>}
            </TabsList>

            <TabsContent
                headingTextMobile={t('Overview')}
                isActive={selectedTab === 0}
                skipInitialAnimation={skipInitialAnimation}
            >
                {description && <UserText htmlContent={description} />}
            </TabsContent>

            {!!parameters.length && (
                <TabsContent headingTextMobile={t('Parameters')} isActive={selectedTab === parametersTabIndex}>
                    <Table className="mx-auto max-w-[700px] border-0 p-0">
                        {sortedIndividualParameters.length > 0 &&
                            sortedIndividualParameters.map((parameter) => (
                                <Row
                                    key={parameter.uuid}
                                    className="border-none bg-tableBackground odd:bg-tableBackgroundContrast"
                                >
                                    <Cell className="hidden w-[240px] px-5 py-2.5 align-top lg:table-cell">
                                        <h6 className="leading-5">{parameter.name}</h6>
                                    </Cell>
                                    <Cell className="px-5 py-2.5 text-sm">
                                        <h6 className="mb-1 lg:hidden">{parameter.name}</h6>
                                        {parameter.values.map((value, index) =>
                                            formatParameterValue(
                                                value.text + (parameter.unit?.name ? ` (${parameter.unit.name})` : ''),
                                                index,
                                            ),
                                        )}
                                    </Cell>
                                </Row>
                            ))}
                        {sortedGroupParameters.map(({ groupName, groupParameters }) => (
                            <Fragment key={groupName}>
                                {isLg && (
                                    <tr>
                                        <td colSpan={2}>
                                            <h4 className="py-5">{groupName}</h4>
                                        </td>
                                    </tr>
                                )}
                                {groupParameters.map((parameter) => (
                                    <Row
                                        key={parameter.uuid}
                                        className="border-none bg-tableBackground odd:bg-tableBackgroundContrast"
                                    >
                                        <Cell className="hidden w-[240px] px-5 py-2.5 align-top lg:table-cell">
                                            <h6 className="leading-5">{parameter.name}</h6>
                                        </Cell>
                                        <Cell className="px-5 py-2.5 text-sm">
                                            <h6 className="mb-1 lg:hidden">{parameter.name}</h6>
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
                            </Fragment>
                        ))}
                    </Table>
                </TabsContent>
            )}

            {!!relatedProducts.length && (
                <TabsContent
                    headingTextMobile={t('Related Products')}
                    isActive={selectedTab === relatedProductsTabIndex}
                >
                    <ProductDetailRelatedProductsTab relatedProducts={relatedProducts} />
                </TabsContent>
            )}

            {!!files.length && (
                <TabsContent headingTextMobile={t('Files')} isActive={selectedTab === filesTabIndex}>
                    <ul className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                        {files.map((file) => (
                            <li key={file.url} className="">
                                <a
                                    className="flex cursor-pointer items-center gap-5 rounded-xl bg-backgroundMore px-5 py-2.5 no-underline"
                                    href={file.url}
                                >
                                    <DownloadIcon className="size-6" />
                                    <h4>{file.anchorText}</h4>
                                </a>
                            </li>
                        ))}
                    </ul>
                </TabsContent>
            )}
        </Tabs>
    );
};
