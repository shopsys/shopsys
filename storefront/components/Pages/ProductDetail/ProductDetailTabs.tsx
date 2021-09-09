import { Tabs, TabsContent, TabsList, TabsListItem } from '../../basic/Tabs';
import { FC } from 'react';
import ShopsysInUserText from 'components/in/ShopsysInUserText';
import Table from 'components/basic/Table';
import { useTranslation } from 'next-i18next';

type ProductDetailTabsProps = {
    description: string;
};

const ProductDetailTabs: FC<ProductDetailTabsProps> = (props) => {
    const { t } = useTranslation();

    return (
        <Tabs>
            <TabsList>
                <TabsListItem>{t<string>('Přehled')}</TabsListItem>
                <TabsListItem>{t<string>('Parametry')}</TabsListItem>
            </TabsList>
            <TabsContent headingTextMobile={t<string>('Přehled')}>
                <ShopsysInUserText htmlContent={props.description} />
            </TabsContent>
            <TabsContent headingTextMobile={t<string>('Parametry')}>
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
