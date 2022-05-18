import { TextCenteredStyled } from './OrderDetail.style';
import Heading from 'components/Basic/Heading';
import TableGrid from 'components/Basic/TableGrid';
import { TableGridColumnsStyled, TableGridColumnStyled } from 'components/Basic/TableGrid/TableGrid.style';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import { HeadingWrapperStyled } from 'components/Layout/SimpleLayout/SimpleLayout.style';
import Webline from 'components/Layout/Webline';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { OrderDetailType } from 'types/orders';
import { formatPrice } from 'utils/formatting';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

type OrderDetailPageProps = {
    order: OrderDetailType;
};

const OrderDetailPage: FC<OrderDetailPageProps> = (props) => {
    const testIdentifier = 'pages-customer-orderdetail-';

    const t = useTypedTranslationFunction();
    const currentDomainConfig = useShopsysSelector((state) => state.domain);
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], currentDomainConfig.url);

    return (
        <>
            <Webline>
                <HeadingWrapperStyled>
                    <Heading type="h1">
                        {t('Order number')} {props.order.number}
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
                                    {props.order.creationDate}
                                </td>
                            </tr>
                        </TableGridColumnStyled>
                        {props.order.trackingNumber !== null && (
                            <TableGridColumnStyled>
                                <tr>
                                    <th colSpan={2}>{t('Tracking package')}</th>
                                </tr>
                                <tr>
                                    <td>{t('Package number')}:</td>
                                    <td className="text-right" data-testid={testIdentifier + 'trackingUrl'}>
                                        {props.order.trackingUrl !== null && (
                                            <NextLink href={props.order.trackingUrl} passHref>
                                                <a target="_blank">{props.order.trackingNumber}</a>
                                            </NextLink>
                                        )}
                                        {props.order.trackingUrl === null && props.order.trackingNumber}
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
                            {props.order.companyName !== '' && (
                                <>
                                    <tr>
                                        <td>{t('Company name')}:</td>
                                        <td data-testid={testIdentifier + 'companyName'}>{props.order.companyName}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Company number')}:</td>
                                        <td data-testid={testIdentifier + 'companyNumber'}>
                                            {props.order.companyNumber}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{t('Tax number')}:</td>
                                        <td data-testid={testIdentifier + 'companyTaxNumber'}>
                                            {props.order.companyTaxNumber}
                                        </td>
                                    </tr>
                                </>
                            )}
                            {props.order.firstName !== '' && (
                                <>
                                    <tr>
                                        <td>{t('First name')}:</td>
                                        <td data-testid={testIdentifier + 'firstName'}>{props.order.firstName}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Last name')}:</td>
                                        <td data-testid={testIdentifier + 'lastName'}>{props.order.lastName}</td>
                                    </tr>
                                </>
                            )}
                            <tr>
                                <td>{t('Email')}:</td>
                                <td data-testid={testIdentifier + 'email'}>{props.order.email}</td>
                            </tr>
                            <tr>
                                <td>{t('Phone')}:</td>
                                <td data-testid={testIdentifier + 'telephone'}>{props.order.telephone}</td>
                            </tr>
                            <tr>
                                <td>{t('Street and house no.')}:</td>
                                <td data-testid={testIdentifier + 'street'}>{props.order.street}</td>
                            </tr>
                            <tr>
                                <td>{t('City')}:</td>
                                <td data-testid={testIdentifier + 'city'}>{props.order.city}</td>
                            </tr>
                            <tr>
                                <td>{t('Postcode')}:</td>
                                <td data-testid={testIdentifier + 'postcode'}>{props.order.postcode}</td>
                            </tr>
                            <tr>
                                <td>{t('Country')}:</td>
                                <td data-testid={testIdentifier + 'country'}>{props.order.country}</td>
                            </tr>
                        </TableGridColumnStyled>
                        <TableGridColumnStyled>
                            <tr>
                                <th colSpan={2}>{t('Delivery address')}</th>
                            </tr>
                            <tr>
                                <td>{t('First name')}:</td>
                                <td data-testid={testIdentifier + 'deliveryFirstName'}>
                                    {props.order.deliveryFirstName}
                                </td>
                            </tr>
                            <tr>
                                <td>{t('Last name')}:</td>
                                <td data-testid={testIdentifier + 'deliveryLastName'}>
                                    {props.order.deliveryLastName}
                                </td>
                            </tr>
                            <tr>
                                <td>{t('Phone')}:</td>
                                <td data-testid={testIdentifier + 'deliveryTelephone'}>
                                    {props.order.deliveryTelephone}
                                </td>
                            </tr>
                            <tr>
                                <td>{t('Street and house no.')}:</td>
                                <td data-testid={testIdentifier + 'deliveryStreet'}>{props.order.deliveryStreet}</td>
                            </tr>
                            <tr>
                                <td>{t('City')}:</td>
                                <td data-testid={testIdentifier + 'deliveryCity'}>{props.order.deliveryCity}</td>
                            </tr>
                            <tr>
                                <td>{t('Postcode')}:</td>
                                <td data-testid={testIdentifier + 'deliveryPostcode'}>
                                    {props.order.deliveryPostcode}
                                </td>
                            </tr>
                            <tr>
                                <td>{t('Country')}:</td>
                                <td data-testid={testIdentifier + 'deliveryCountry'}>{props.order.deliveryCountry}</td>
                            </tr>
                        </TableGridColumnStyled>
                    </TableGridColumnsStyled>
                </TableGrid>
                {props.order.items.length > 0 && (
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

                            {props.order.items.map((item, index) => {
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
