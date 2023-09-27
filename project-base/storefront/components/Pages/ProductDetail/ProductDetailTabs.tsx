import { Cell, Row, Table } from 'components/Basic/Table/Table';
import { Tabs, TabsContent, TabsList, TabsListItem } from 'components/Basic/Tabs/Tabs';
import { UserText } from 'components/Basic/UserText/UserText';
import { ParameterFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';

type ProductDetailTabsProps = {
    description: string | null;
    parameters: ParameterFragmentApi[];
};

export const ProductDetailTabs: FC<ProductDetailTabsProps> = ({ description, parameters }) => {
    const { t } = useTranslation();

    const formatParameterValue = (valueText: string, index: number) => {
        return index > 0 ? ' | ' + valueText : valueText;
    };

    return (
        <Tabs className="flex flex-col gap-4 lg:gap-0">
            <TabsList>
                <TabsListItem>{t('Overview')}</TabsListItem>

                {!!parameters.length && <TabsListItem>{t('Parameters')}</TabsListItem>}
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
        </Tabs>
    );
};
