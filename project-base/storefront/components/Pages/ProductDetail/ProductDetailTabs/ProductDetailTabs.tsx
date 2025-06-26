import { DownloadIcon } from 'components/Basic/Icon/DownloadIcon';
import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Tabs, TabsContent, TabsList, TabsListItem } from 'components/Basic/Tabs/Tabs';
import { UserText } from 'components/Basic/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeFileFragment } from 'graphql/requests/files/fragments/FileFragment.generated';
import { TypeParameterFragment } from 'graphql/requests/parameters/fragments/ParameterFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { Fragment, useState } from 'react';

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
        <Webline>
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
                        <div className="mx-auto max-w-[700px]">
                            {sortedGroupParameters.map(({ groupName, groupParameters }) => (
                                <Fragment key={groupName}>
                                    <p className="h4 py-5">{groupName}</p>

                                    <Table>
                                        {groupParameters.map((parameter) => (
                                            <Row
                                                key={parameter.uuid}
                                                className="bg-table-bg-default odd:bg-table-bg-contrast border-none"
                                            >
                                                <Cell className="hidden w-[240px] px-5 py-2.5 align-top lg:table-cell">
                                                    <span className="h6 leading-5">{parameter.name}</span>
                                                </Cell>

                                                <Cell className="px-5 py-2.5 text-sm">
                                                    <span className="h6 leading-5 lg:hidden">{parameter.name}</span>

                                                    {parameter.values.map((value, index) =>
                                                        formatParameterValue(
                                                            value.text +
                                                                (parameter.unit?.name ? ` ${parameter.unit.name}` : ''),
                                                            index,
                                                        ),
                                                    )}
                                                </Cell>
                                            </Row>
                                        ))}
                                    </Table>
                                </Fragment>
                            ))}

                            {sortedIndividualParameters.length > 0 && (
                                <Fragment key="other-parameters">
                                    {!!sortedGroupParameters.length && (
                                        <p className="h4 py-5">{t('Other parameters')}</p>
                                    )}

                                    <Table>
                                        {sortedIndividualParameters.map((parameter) => (
                                            <Fragment key={parameter.uuid}>
                                                <Row
                                                    key={parameter.uuid}
                                                    className="bg-table-bg-default odd:bg-table-bg-contrast border-none"
                                                >
                                                    <Cell className="hidden w-[240px] px-5 py-2.5 align-top lg:table-cell">
                                                        <span className="h6 leading-5">{parameter.name}</span>
                                                    </Cell>

                                                    <Cell className="px-5 py-2.5 text-sm">
                                                        <span className="h6 leading-5 lg:hidden">{parameter.name}</span>

                                                        {parameter.values.map((value, index) =>
                                                            formatParameterValue(
                                                                value.text +
                                                                    (parameter.unit?.name
                                                                        ? ` ${parameter.unit.name}`
                                                                        : ''),
                                                                index,
                                                            ),
                                                        )}
                                                    </Cell>
                                                </Row>
                                            </Fragment>
                                        ))}
                                    </Table>
                                </Fragment>
                            )}
                        </div>
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
                                        aria-label={t('Download {{file}}', { file: file.anchorText })}
                                        className="bg-background-more flex cursor-pointer items-center gap-5 rounded-xl px-5 py-2.5 no-underline"
                                        href={file.url}
                                    >
                                        <DownloadIcon className="size-6" />

                                        <span className="h4">{file.anchorText}</span>
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </TabsContent>
                )}
            </Tabs>
        </Webline>
    );
};
