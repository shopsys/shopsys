import { TableGridColumnsStyled, TableGridColumnStyled } from 'components/Basic/TableGrid/TableGrid.style';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import { getOrderDetail } from 'connectors/customer/Orders';
import Heading from 'components/Basic/Heading';
import { HeadingWrapperStyled } from 'components/Layout/SimpleLayout/SimpleLayout.style';
import NextLink from 'next/link';
import TableGrid from 'components/Basic/TableGrid';
import { TextCenteredStyled } from './OrderDetail.style';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type OrderDetailPageProps = {
    orderNumber: string;
};

const OrderDetailPage: FC<OrderDetailPageProps> = (props) => {
    const testIdentifier = 'pages-customer-orderdetail-';

    const t = useTypedTranslationFunction();
    const currentDomainConfig = useShopsysSelector((state) => state.domain);
    const [customerOrdersUrl] = useGetInternationalizedStaticUrls(['/customer/orders'], currentDomainConfig.url);
    const order = getOrderDetail(props.orderNumber, currentDomainConfig);

    if (order === null) {
        return (
            <Webline>
                <TableGrid>
                    <tr>
                        <th>{t('Error occured when loading order detail')}</th>
                    </tr>
                </TableGrid>
            </Webline>
        );
    }

    return (
        <>
            <Webline>
                <HeadingWrapperStyled>
                    <Heading type="h1">
                        {t('Order number')} {order.number}
                    </Heading>
                </HeadingWrapperStyled>
                <Breadcrumbs key="breadcrumb" breadcrumb={[{ name: t('My orders'), slug: customerOrdersUrl }]} />
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
                                <td className="text-right" data-testid={testIdentifier + 'creationDate'}>
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
                                    <td className="text-right" data-testid={testIdentifier + 'trackingUrl'}>
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
                                        <td data-testid={testIdentifier + 'companyName'}>{order.companyName}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Company number')}:</td>
                                        <td data-testid={testIdentifier + 'companyNumber'}>{order.companyNumber}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Tax number')}:</td>
                                        <td data-testid={testIdentifier + 'companyTaxNumber'}>
                                            {order.companyTaxNumber}
                                        </td>
                                    </tr>
                                </>
                            )}
                            {order.firstName !== '' && (
                                <>
                                    <tr>
                                        <td>{t('First name')}:</td>
                                        <td data-testid={testIdentifier + 'firstName'}>{order.firstName}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Last name')}:</td>
                                        <td data-testid={testIdentifier + 'lastName'}>{order.lastName}</td>
                                    </tr>
                                </>
                            )}
                            <tr>
                                <td>{t('Email')}:</td>
                                <td data-testid={testIdentifier + 'email'}>{order.email}</td>
                            </tr>
                            <tr>
                                <td>{t('Phone')}:</td>
                                <td data-testid={testIdentifier + 'telephone'}>{order.telephone}</td>
                            </tr>
                            <tr>
                                <td>{t('Street and house no.')}:</td>
                                <td data-testid={testIdentifier + 'street'}>{order.street}</td>
                            </tr>
                            <tr>
                                <td>{t('City')}:</td>
                                <td data-testid={testIdentifier + 'city'}>{order.city}</td>
                            </tr>
                            <tr>
                                <td>{t('Postcode')}:</td>
                                <td data-testid={testIdentifier + 'postcode'}>{order.postcode}</td>
                            </tr>
                            <tr>
                                <td>{t('Country')}:</td>
                                <td data-testid={testIdentifier + 'country'}>{order.country}</td>
                            </tr>
                        </TableGridColumnStyled>
                        <TableGridColumnStyled>
                            <tr>
                                <th colSpan={2}>{t('Delivery address')}</th>
                            </tr>
                            <tr>
                                <td>{t('First name')}:</td>
                                <td data-testid={testIdentifier + 'deliveryFirstName'}>{order.deliveryFirstName}</td>
                            </tr>
                            <tr>
                                <td>{t('Last name')}:</td>
                                <td data-testid={testIdentifier + 'deliveryLastName'}>{order.deliveryLastName}</td>
                            </tr>
                            <tr>
                                <td>{t('Phone')}:</td>
                                <td data-testid={testIdentifier + 'deliveryTelephone'}>{order.deliveryTelephone}</td>
                            </tr>
                            <tr>
                                <td>{t('Street and house no.')}:</td>
                                <td data-testid={testIdentifier + 'deliveryStreet'}>{order.deliveryStreet}</td>
                            </tr>
                            <tr>
                                <td>{t('City')}:</td>
                                <td data-testid={testIdentifier + 'deliveryCity'}>{order.deliveryCity}</td>
                            </tr>
                            <tr>
                                <td>{t('Postcode')}:</td>
                                <td data-testid={testIdentifier + 'deliveryPostcode'}>{order.deliveryPostcode}</td>
                            </tr>
                            <tr>
                                <td>{t('Country')}:</td>
                                <td data-testid={testIdentifier + 'deliveryCountry'}>{order.deliveryCountry}</td>
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
                                    <tr key={index} data-testid={testIdentifier + 'item-' + index}>
                                        <td data-testid={testIdentifier + 'item-name'}>{item.name}</td>
                                        <td className="text-right" data-testid={testIdentifier + 'item-unitprice'}>
                                            {formatPrice(
                                                item.unitPrice.priceWithVat,
                                                currentDomainConfig.currencyCode,
                                                t,
                                            )}
                                        </td>
                                        <td className="text-right" data-testid={testIdentifier + 'item-quantity'}>
                                            {item.quantity} {item.unit}
                                        </td>
                                        <td className="text-right nowrap" data-testid={testIdentifier + 'item-vat'}>
                                            {parseFloat(item.vatRate)} %
                                        </td>
                                        <td className="text-right" data-testid={testIdentifier + 'item-price'}>
                                            {formatPrice(
                                                item.totalPrice.priceWithoutVat,
                                                currentDomainConfig.currencyCode,
                                                t,
                                            )}
                                        </td>
                                        <td className="text-right" data-testid={testIdentifier + 'item-pricevat'}>
                                            {formatPrice(
                                                item.totalPrice.priceWithVat,
                                                currentDomainConfig.currencyCode,
                                                t,
                                            )}
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
