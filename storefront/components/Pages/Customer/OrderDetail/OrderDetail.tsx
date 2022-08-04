import { TextCenteredStyled } from './OrderDetail.style';
import Heading from 'components/Basic/Heading';
import TableGrid from 'components/Basic/TableGrid';
import { TableGridColumnsStyled, TableGridColumnStyled } from 'components/Basic/TableGrid/TableGrid.style';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import { HeadingWrapperStyled } from 'components/Layout/SimpleLayout/SimpleLayout.style';
import Webline from 'components/Layout/Webline';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { OrderDetailType } from 'types/orders';

type OrderDetailPageProps = {
    order: OrderDetailType;
    breadcrumbs: BreadcrumbItemType[];
};

const TEST_IDENTIFIER = 'pages-customer-orderdetail-';

const OrderDetailPage: FC<OrderDetailPageProps> = ({ order, breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    return (
        <>
            <Webline>
                <HeadingWrapperStyled>
                    <Heading type="h1">
                        {t('Order number')} {order.number}
                    </Heading>
                </HeadingWrapperStyled>
                <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumbs} />
            </Webline>
            <Webline>
                <TableGrid>
                    <TableGridColumnsStyled>
                        <TableGridColumnStyled>
                            <tr>
                                <th colSpan={2}>{t('Basic information')}</th>
                            </tr>
                            <tr>
                                <td>{t('Creation date')}:</td>
                                <td className="text-right" data-testid={TEST_IDENTIFIER + 'creationDate'}>
                                    {order.creationDate}
                                </td>
                            </tr>
                        </TableGridColumnStyled>
                        {order.trackingNumber !== null && (
                            <TableGridColumnStyled>
                                <tr>
                                    <th colSpan={2}>{t('Tracking package')}</th>
                                </tr>
                                <tr>
                                    <td>{t('Package number')}:</td>
                                    <td className="text-right" data-testid={TEST_IDENTIFIER + 'trackingUrl'}>
                                        {order.trackingUrl !== null && (
                                            <NextLink href={order.trackingUrl} passHref>
                                                <a target="_blank">{order.trackingNumber}</a>
                                            </NextLink>
                                        )}
                                        {order.trackingUrl === null && order.trackingNumber}
                                    </td>
                                </tr>
                            </TableGridColumnStyled>
                        )}
                        {!!order.note && (
                            <TableGridColumnStyled>
                                <tr>
                                    <th colSpan={2}>{t('Your note')}</th>
                                </tr>
                                <tr>
                                    <td data-testid={TEST_IDENTIFIER + 'note'}>{order.note}</td>
                                </tr>
                            </TableGridColumnStyled>
                        )}
                    </TableGridColumnsStyled>
                </TableGrid>
                <TableGrid>
                    <TableGridColumnsStyled>
                        <TableGridColumnStyled>
                            <tr>
                                <th colSpan={2}>{t('Billing address')}</th>
                            </tr>
                            {order.companyName !== '' && (
                                <>
                                    <tr>
                                        <td>{t('Company name')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'companyName'}>{order.companyName}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Company number')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'companyNumber'}>{order.companyNumber}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Tax number')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'companyTaxNumber'}>
                                            {order.companyTaxNumber}
                                        </td>
                                    </tr>
                                </>
                            )}
                            {order.firstName !== '' && (
                                <>
                                    <tr>
                                        <td>{t('First name')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'firstName'}>{order.firstName}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Last name')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'lastName'}>{order.lastName}</td>
                                    </tr>
                                </>
                            )}
                            <tr>
                                <td>{t('Email')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'email'}>{order.email}</td>
                            </tr>
                            <tr>
                                <td>{t('Phone')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'telephone'}>{order.telephone}</td>
                            </tr>
                            <tr>
                                <td>{t('Street and house no.')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'street'}>{order.street}</td>
                            </tr>
                            <tr>
                                <td>{t('City')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'city'}>{order.city}</td>
                            </tr>
                            <tr>
                                <td>{t('Postcode')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'postcode'}>{order.postcode}</td>
                            </tr>
                            <tr>
                                <td>{t('Country')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'country'}>{order.country}</td>
                            </tr>
                        </TableGridColumnStyled>
                        <TableGridColumnStyled>
                            <tr>
                                <th colSpan={2}>{t('Delivery address')}</th>
                            </tr>
                            <tr>
                                <td>{t('First name')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'deliveryFirstName'}>{order.deliveryFirstName}</td>
                            </tr>
                            <tr>
                                <td>{t('Last name')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'deliveryLastName'}>{order.deliveryLastName}</td>
                            </tr>
                            {order.deliveryCompanyName !== '' && (
                                <tr>
                                    <td>{t('Company name')}:</td>
                                    <td data-testid={TEST_IDENTIFIER + 'deliveryCompanyName'}>
                                        {order.deliveryCompanyName}
                                    </td>
                                </tr>
                            )}
                            <tr>
                                <td>{t('Phone')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'deliveryTelephone'}>{order.deliveryTelephone}</td>
                            </tr>
                            <tr>
                                <td>{t('Street and house no.')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'deliveryStreet'}>{order.deliveryStreet}</td>
                            </tr>
                            <tr>
                                <td>{t('City')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'deliveryCity'}>{order.deliveryCity}</td>
                            </tr>
                            <tr>
                                <td>{t('Postcode')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'deliveryPostcode'}>{order.deliveryPostcode}</td>
                            </tr>
                            <tr>
                                <td>{t('Country')}:</td>
                                <td data-testid={TEST_IDENTIFIER + 'deliveryCountry'}>{order.deliveryCountry}</td>
                            </tr>
                        </TableGridColumnStyled>
                    </TableGridColumnsStyled>
                </TableGrid>
                {order.items.length > 0 && (
                    <>
                        <TextCenteredStyled>
                            <Heading type="h2">{t('Your purchase')}</Heading>
                        </TextCenteredStyled>
                        <TableGrid>
                            <tr>
                                <th>{t('Product name')}</th>
                                <th className="text-right">{t('Price per piece incl. VAT')}</th>
                                <th className="text-right">{t('Amount')}</th>
                                <th className="text-right">{t('VAT')}</th>
                                <th className="text-right">{t('Total price excl. VAT')}</th>
                                <th className="text-right">{t('Total price incl. VAT')}</th>
                            </tr>

                            {order.items.map((item, index) => {
                                return (
                                    <tr key={index} data-testid={TEST_IDENTIFIER + 'item-' + index}>
                                        <td data-testid={TEST_IDENTIFIER + 'item-name'}>{item.name}</td>
                                        <td className="text-right" data-testid={TEST_IDENTIFIER + 'item-unitprice'}>
                                            {formatPrice(item.unitPrice.priceWithVat)}
                                        </td>
                                        <td className="text-right" data-testid={TEST_IDENTIFIER + 'item-quantity'}>
                                            {item.quantity} {item.unit}
                                        </td>
                                        <td className="text-right nowrap" data-testid={TEST_IDENTIFIER + 'item-vat'}>
                                            {parseFloat(item.vatRate)} %
                                        </td>
                                        <td className="text-right" data-testid={TEST_IDENTIFIER + 'item-price'}>
                                            {formatPrice(item.totalPrice.priceWithoutVat)}
                                        </td>
                                        <td className="text-right" data-testid={TEST_IDENTIFIER + 'item-pricevat'}>
                                            {formatPrice(item.totalPrice.priceWithVat)}
                                        </td>
                                    </tr>
                                );
                            })}
                        </TableGrid>
                    </>
                )}
            </Webline>
        </>
    );
};

export default OrderDetailPage;
