import { Table } from 'components/Basic/Table/Table';
import { Tabs, TabsContent, TabsList, TabsListItem } from 'components/Basic/Tabs/Tabs';
import { UserText } from 'components/Helpers/UserText/UserText';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { ProductParameterType } from 'types/parameter';

type ProductDetailTabsProps = {
    description: string;
    parameters: ProductParameterType[];
};

const TEST_IDENTIFIER = 'pages-productdetail-';

export const ProductDetailTabs: FC<ProductDetailTabsProps> = ({ description, parameters }) => {
    const t = useTypedTranslationFunction();

    const formatParameterValue = (valueText: string, index: number) => {
        return index > 0 ? ' | ' + valueText : valueText;
    };

    return (
        <Tabs>
            <TabsList>
                <TabsListItem testIdentifier={TEST_IDENTIFIER + 'overview-link'}>{t('Overview')}</TabsListItem>
                {parameters.length > 0 && (
                    <TabsListItem testIdentifier={TEST_IDENTIFIER + 'parameters-link'}>{t('Parameters')}</TabsListItem>
                )}
            </TabsList>
            <TabsContent headingTextMobile={t('Overview')} testIdentifier={TEST_IDENTIFIER + 'overview-content'}>
                <UserText htmlContent={description} />
            </TabsContent>
            {parameters.length > 0 && (
                <TabsContent
                    headingTextMobile={t('Parameters')}
                    testIdentifier={TEST_IDENTIFIER + 'parameters-content'}
                >
                    <Table>
                        <tbody>
                            {parameters.map((parameter) => (
                                <tr key={parameter.uuid}>
                                    <th>{parameter.name}</th>
                                    <td>
                                        {parameter.values.map((value, index) =>
                                            formatParameterValue(value.text, index),
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </Table>
                </TabsContent>
            )}
        </Tabs>
    );
};
