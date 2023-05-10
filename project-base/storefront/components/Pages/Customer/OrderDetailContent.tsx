import { Heading } from 'components/Basic/Heading/Heading';
import { TableGrid, TableGridColumn } from 'components/Basic/TableGrid/TableGrid';
import { TableGridColumns } from 'components/Basic/TableGrid/TableGridElements';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { BreadcrumbFragmentApi, OrderDetailFragmentApi } from 'graphql/generated';
import { formatDateAndTime } from 'helpers/formaters/formatDate';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';

type OrderDetailContentProps = {
    order: OrderDetailFragmentApi;
    breadcrumbs: BreadcrumbFragmentApi[];
};

const TEST_IDENTIFIER = 'pages-customer-orderdetail-';

export const OrderDetailContent: FC<OrderDetailContentProps> = ({ order, breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    return (
        <>
            <Webline>
                <div className="text-center">
                    <Heading type="h1">
                        {t('Order number')} {order.number}
                    </Heading>
                </div>
                <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumbs} />
            </Webline>
            <Webline>
                <TableGrid>
                    <TableGridColumns>
                        <TableGridColumn>
                            <tr>
                                <th colSpan={2}>{t('Basic information')}</th>
                            </tr>
                            <tr>
                                <td>{t('Creation date')}:</td>
                                <td className="text-right" data-testid={TEST_IDENTIFIER + 'creationDate'}>
                                    {formatDateAndTime(order.creationDate)}
                                </td>
                            </tr>
                        </TableGridColumn>
                        {order.trackingNumber && (
                            <TableGridColumn>
                                <tr>
                                    <th colSpan={2}>{t('Tracking package')}</th>
                                </tr>
                                <tr>
                                    <td>{t('Package number')}:</td>
                                    <td className="text-right" data-testid={TEST_IDENTIFIER + 'trackingUrl'}>
                                        {order.trackingUrl && (
                                            <NextLink href={order.trackingUrl} passHref>
                                                <a target="_blank">{order.trackingNumber}</a>
                                            </NextLink>
                                        )}
                                        {order.trackingUrl === null && order.trackingNumber}
                                    </td>
                                </tr>
                            </TableGridColumn>
                        )}
                        {order.note && (
                            <TableGridColumn>
                                <tr>
                                    <th colSpan={2}>{t('Your note')}</th>
                                </tr>
                                <tr>
                                    <td data-testid={TEST_IDENTIFIER + 'note'}>{order.note}</td>
                                </tr>
                            </TableGridColumn>
                        )}
                    </TableGridColumns>
                </TableGrid>
                <TableGrid>
                    <TableGridColumns>
                        <TableGridColumn>
                            <tr>
                                <th colSpan={2}>{t('Billing address')}</th>
                            </tr>
                            {!!order.companyName && (
                                <>
                                    <tr>
                                        <td>{t('Company name')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'companyName'}>{order.companyName}</td>
                                    </tr>
                                    <tr>
                                        <td>{t('Company number')}:</td>
                                        <td data-testid={TEST_IDENTIFIER + 'companyNumber'}>{order.companyNumber}</td>
                                    </tr>
                                    {!!order.companyTaxNumber && (
                                        <tr>
                                            <td>{t('Tax number')}:</td>
                                            <td data-testid={TEST_IDENTIFIER + 'companyTaxNumber'}>
                                                {order.companyTaxNumber}
                                            </td>
                                        </tr>
                                    )}
                                </>
                            )}
                            {!!order.firstName && (
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
                                <td data-testid={TEST_IDENTIFIER + 'country'}>{order.country.name}</td>
                            </tr>
                        </TableGridColumn>
                        <TableGridColumn>
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
                            {!!order.deliveryCompanyName && (
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
                                <td data-testid={TEST_IDENTIFIER + 'deliveryCountry'}>{order.deliveryCountry?.name}</td>
                            </tr>
                        </TableGridColumn>
                    </TableGridColumns>
                </TableGrid>
                {order.items.length > 0 && (
                    <>
                        <div className="text-center">
                            <Heading type="h2">{t('Your purchase')}</Heading>
                        </div>
                        <TableGrid>
                            <tr>
                                <th>{t('Product name')}</th>
                                <th className="text-right">{t('Price per piece incl. VAT')}</th>
                                <th className="text-right">{t('Amount')}</th>
                                <th className="text-right">{t('VAT')}</th>
                                <th className="text-right">{t('Total price excl. VAT')}</th>
                                <th className="text-right">{t('Total price incl. VAT')}</th>
                            </tr>

                            {order.items.map((item, index) => (
                                <tr key={index} data-testid={TEST_IDENTIFIER + 'item-' + index}>
                                    <td data-testid={TEST_IDENTIFIER + 'item-name'}>{item.name}</td>
                                    <td className="text-right" data-testid={TEST_IDENTIFIER + 'item-unitprice'}>
                                        {formatPrice(item.unitPrice.priceWithVat)}
                                    </td>
                                    <td className="text-right" data-testid={TEST_IDENTIFIER + 'item-quantity'}>
                                        {item.quantity} {item.unit}
                                    </td>
                                    <td className="nowrap text-right" data-testid={TEST_IDENTIFIER + 'item-vat'}>
                                        {parseFloat(item.vatRate)} %
                                    </td>
                                    <td className="text-right" data-testid={TEST_IDENTIFIER + 'item-price'}>
                                        {formatPrice(item.totalPrice.priceWithoutVat)}
                                    </td>
                                    <td className="text-right" data-testid={TEST_IDENTIFIER + 'item-pricevat'}>
                                        {formatPrice(item.totalPrice.priceWithVat)}
                                    </td>
                                </tr>
                            ))}
                        </TableGrid>
                    </>
                )}
            </Webline>
        </>
    );
};
