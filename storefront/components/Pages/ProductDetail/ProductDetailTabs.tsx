import { Tabs, TabsContent, TabsList, TabsListItem } from '../../Basic/Tabs';
import { FC } from 'react';
import Table from '../../Basic/Table';
import UserText from 'components/Helpers/UserText';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ProductDetailTabsProps = {
    description: string;
};

const ProductDetailTabs: FC<ProductDetailTabsProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <Tabs>
            <TabsList>
                <TabsListItem>{t('Přehled')}</TabsListItem>
                <TabsListItem>{t('Parametry')}</TabsListItem>
            </TabsList>
            <TabsContent headingTextMobile={t('Přehled')}>
                <UserText htmlContent={props.description} />
            </TabsContent>
            <TabsContent headingTextMobile={t('Parametry')}>
                <Table>
                    <tbody>
                        <tr>
                            <th>Úhlopříčka</th>
                            <td>27&quot;</td>
                        </tr>
                        <tr>
                            <th>Technologie</th>
                            <td>LED</td>
                        </tr>
                        <tr>
                            <th>Rozlišení</th>
                            <td>1920x1080 (Full HD)</td>
                        </tr>
                        <tr>
                            <th>USB</th>
                            <td>Ano</td>
                        </tr>
                        <tr>
                            <th>HDMI</th>
                            <td>Ano</td>
                        </tr>
                        <tr>
                            <th>Barva</th>
                            <td>Černá</td>
                        </tr>
                        <tr>
                            <th>Materiál</th>
                            <td>kov</td>
                        </tr>
                    </tbody>
                </Table>
            </TabsContent>
        </Tabs>
    );
};

export default ProductDetailTabs;
