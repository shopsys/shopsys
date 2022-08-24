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

export const ProductDetailTabs: FC<ProductDetailTabsProps> = (props) => {
    const testIdentifier = 'pages-productdetail-';

    const t = useTypedTranslationFunction();

    const formatParameterValue = (valueText: string, index: number) => {
        return index > 0 ? ' | ' + valueText : valueText;
    };

    return (
        <Tabs>
            <TabsList>
                <TabsListItem data-testid={testIdentifier + 'overview-link'}>{t('Overview')}</TabsListItem>
                {props.parameters.length > 0 && (
                    <TabsListItem data-testid={testIdentifier + 'parameters-link'}>{t('Parameters')}</TabsListItem>
                )}
            </TabsList>
            <TabsContent headingTextMobile={t('Overview')} data-testid={testIdentifier + 'overview-content'}>
                <UserText htmlContent={props.description} />
            </TabsContent>
            {props.parameters.length > 0 && (
                <TabsContent headingTextMobile={t('Parameters')} data-testid={testIdentifier + 'parameters-content'}>
                    <Table>
                        <tbody>
                            {props.parameters.map((parameter) => (
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
