import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Tabs, TabsContent, TabsList, TabsListItem } from 'components/Basic/Tabs/Tabs';
import { UserText } from 'components/Basic/UserText/UserText';
import { ParameterFragmentApi } from 'graphql/requests/parameters/fragments/ParameterFragment.generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';

type ProductDetailTabsProps = {
    description: string | null;
    parameters: ParameterFragmentApi[];
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
                <TabsListItem dataTestId={TEST_IDENTIFIER + 'overview-link'}>{t('Overview')}</TabsListItem>
                {parameters.length > 0 && (
                    <TabsListItem dataTestId={TEST_IDENTIFIER + 'parameters-link'}>{t('Parameters')}</TabsListItem>
                )}
            </TabsList>
            <TabsContent headingTextMobile={t('Overview')} dataTestId={TEST_IDENTIFIER + 'overview-content'}>
                {description !== null && <UserText htmlContent={description} />}
            </TabsContent>
            {parameters.length > 0 && (
                <TabsContent headingTextMobile={t('Parameters')} dataTestId={TEST_IDENTIFIER + 'parameters-content'}>
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
        </Tabs>
    );
};
