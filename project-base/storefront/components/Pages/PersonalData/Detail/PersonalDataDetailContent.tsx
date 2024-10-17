import { Link } from 'components/Basic/Link/Link';
import { Cell, CellHead, CellMinor, Row, Table } from 'components/Basic/Table/Table';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypePersonalDataDetailQuery } from 'graphql/requests/personalData/queries/PersonalDataDetailQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

type PersonalDataDetailContentProps = {
    personalDataDetail: TypePersonalDataDetailQuery;
};

export const PersonalDataDetailContent: FC<PersonalDataDetailContentProps> = ({ personalDataDetail }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDate } = useFormatDate();

    const userData = personalDataDetail.accessPersonalData.customerUser;
    const orders = personalDataDetail.accessPersonalData.orders;
    const newsLetterSubscriber = personalDataDetail.accessPersonalData.newsletterSubscriber;
    const exportLink = personalDataDetail.accessPersonalData.exportLink;
    const complaints = personalDataDetail.accessPersonalData.complaints;

    return (
        <>
            <div className="text-center">
                <h1>{t('Personal data')}</h1>
                <Link isButton isExternal href={exportLink} size="small">
                    {t('Download as XML')}
                </Link>
            </div>

            {!!userData && (
                <Webline className="mt-6">
                    <div className="h2 mb-3 mt-6">{t('Billing address')}</div>
                    <Table>
                        <Row className="flex flex-col md:flex-row">
                            <Cell className="flex-1">
                                <Table className="border-0 p-0">
                                    {!!userData.firstName && (
                                        <>
                                            <Row>
                                                <CellMinor>{t('First name')}:</CellMinor>
                                                <Cell>{userData.firstName}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Last name')}:</CellMinor>
                                                <Cell>{userData.lastName}</Cell>
                                            </Row>
                                        </>
                                    )}
                                    <Row>
                                        <CellMinor>{t('Email')}</CellMinor>
                                        <Cell>{userData.email}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Phone')}</CellMinor>
                                        <Cell>{userData.telephone}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Street and house no.')}</CellMinor>
                                        <Cell>{userData.street}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('City')}</CellMinor>
                                        <Cell>{userData.city}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Postcode')}</CellMinor>
                                        <Cell>{userData.postcode}</Cell>
                                    </Row>
                                    <Row>
                                        <CellMinor>{t('Country')}</CellMinor>
                                        <Cell>{userData.country?.name ?? ''}</Cell>
                                    </Row>
                                </Table>
                            </Cell>

                            {userData.__typename === 'CompanyCustomerUser' && (
                                <Cell className="flex-1">
                                    <Table className="border-0 p-0">
                                        <Row>
                                            <CellMinor>{t('Company name')}</CellMinor>
                                            <Cell>{userData.companyName}</Cell>
                                        </Row>
                                        <Row>
                                            <CellMinor>{t('Company number')}</CellMinor>
                                            <Cell>{userData.companyNumber}</Cell>
                                        </Row>
                                        <Row>
                                            <CellMinor>{t('Tax number')}</CellMinor>
                                            <Cell>{userData.companyTaxNumber}</Cell>
                                        </Row>
                                    </Table>
                                </Cell>
                            )}
                        </Row>
                    </Table>

                    {userData.deliveryAddresses.length > 0 && (
                        <>
                            <div className="h2 mb-3 mt-6">{t('Delivery addresses')}</div>
                            <Table>
                                {userData.deliveryAddresses.map((address) => (
                                    <Row key={address.uuid}>
                                        <Cell>
                                            {address.firstName} {address.lastName}
                                            {address.companyName ? ` (${address.companyName})` : ''}, {address.street},{' '}
                                            {address.postcode} {address.city}, {address.country?.name}
                                            {address.telephone ? `, ${t('Phone')}: ${address.telephone}` : ''}
                                        </Cell>
                                    </Row>
                                ))}
                            </Table>
                        </>
                    )}
                </Webline>
            )}

            <Webline className="mt-6">
                <h2 className="mb-3">{t('My orders')}</h2>

                {orders.length ? (
                    <div className="flex flex-col gap-4">
                        {orders.map((order) => (
                            <Table key={order.uuid}>
                                <Row className="flex flex-col md:flex-row">
                                    <Cell className="flex-1">
                                        <Table className="border-0 p-0">
                                            <Row>
                                                <CellMinor>{t('Order number')}</CellMinor>
                                                <Cell>{order.number}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Creation date')}</CellMinor>
                                                <Cell>{formatDate(order.creationDate)}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('First name')}</CellMinor>
                                                <Cell>{order.firstName}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Last name')}</CellMinor>
                                                <Cell>{order.lastName}</Cell>
                                            </Row>
                                            {!!order.companyName && (
                                                <Row>
                                                    <CellMinor>{t('Company')}</CellMinor>
                                                    <Cell>{order.companyName}</Cell>
                                                </Row>
                                            )}
                                            {!!order.companyNumber && (
                                                <Row>
                                                    <CellMinor>{t('Company number')}</CellMinor>
                                                    <Cell>{order.companyNumber}</Cell>
                                                </Row>
                                            )}
                                            {!!order.companyTaxNumber && (
                                                <Row>
                                                    <CellMinor>{t('Tax number')}</CellMinor>
                                                    <Cell>{order.companyTaxNumber}</Cell>
                                                </Row>
                                            )}
                                            <Row>
                                                <CellMinor>{t('Phone')}</CellMinor>
                                                <Cell>{order.telephone}</Cell>
                                            </Row>
                                            {!!order.deliveryFirstName && (
                                                <Row>
                                                    <CellMinor>{t('Delivery address')}</CellMinor>
                                                    <Cell>
                                                        {order.deliveryFirstName} {order.deliveryLastName}
                                                        {order.deliveryCompanyName
                                                            ? ` (${order.deliveryCompanyName})`
                                                            : ''}
                                                        , {order.deliveryStreet}, {order.deliveryPostcode}{' '}
                                                        {order.deliveryCity}, {order.deliveryCountry?.name}
                                                        {order.deliveryTelephone
                                                            ? `, ${t('Phone')}: ${order.deliveryTelephone}`
                                                            : ''}
                                                    </Cell>
                                                </Row>
                                            )}
                                        </Table>
                                    </Cell>

                                    <Cell className="flex-1">
                                        <Table className="border-0 p-0">
                                            <Row>
                                                <CellMinor>{t('Number of items')}</CellMinor>
                                                <Cell>
                                                    {order.productItems.reduce((sum, item) => sum + item.quantity, 0)}
                                                </Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Shipping')}</CellMinor>
                                                <Cell>{order.transport.name}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Payment')}</CellMinor>
                                                <Cell>{order.payment.name}</Cell>
                                            </Row>
                                            {isPriceVisible(order.totalPrice.priceWithVat) && (
                                                <Row>
                                                    <CellMinor>{t('Total price including VAT')}</CellMinor>
                                                    <Cell>
                                                        {formatPrice(parseFloat(order.totalPrice.priceWithVat))}
                                                    </Cell>
                                                </Row>
                                            )}
                                        </Table>
                                    </Cell>
                                </Row>
                            </Table>
                        ))}
                    </div>
                ) : (
                    <p>{t('You have no orders')}</p>
                )}
            </Webline>

            <Webline className="mt-6">
                <h2 className="mb-3">{t('My complaints')}</h2>

                {complaints.length ? (
                    <div className="flex flex-col gap-4">
                        {complaints.map((complaint) => (
                            <Table key={complaint.uuid}>
                                <Row className="flex flex-col md:flex-row">
                                    <Cell className="flex-1">
                                        <Table className="border-0 p-0">
                                            <Row>
                                                <CellMinor>{t('Complaint number')}</CellMinor>
                                                <Cell>{complaint.number}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Creation date')}</CellMinor>
                                                <Cell>{formatDate(complaint.createdAt, 'l')}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Status')}</CellMinor>
                                                <Cell>{complaint.status}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('First name')}</CellMinor>
                                                <Cell>{complaint.deliveryFirstName}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Last name')}</CellMinor>
                                                <Cell>{complaint.deliveryLastName}</Cell>
                                            </Row>
                                            {!!complaint.deliveryCompanyName && (
                                                <Row>
                                                    <CellMinor>{t('Company')}</CellMinor>
                                                    <Cell>{complaint.deliveryCompanyName}</Cell>
                                                </Row>
                                            )}
                                            <Row>
                                                <CellMinor>{t('Phone')}</CellMinor>
                                                <Cell>{complaint.deliveryTelephone}</Cell>
                                            </Row>
                                            <Row>
                                                <CellMinor>{t('Delivery address')}</CellMinor>
                                                <Cell>
                                                    {complaint.deliveryFirstName} {complaint.deliveryLastName}
                                                    {complaint.deliveryCompanyName
                                                        ? ` (${complaint.deliveryCompanyName})`
                                                        : ''}
                                                    , {complaint.deliveryStreet}, {complaint.deliveryPostcode}{' '}
                                                    {complaint.deliveryCity}, {complaint.deliveryCountry.name}
                                                    {complaint.deliveryTelephone
                                                        ? `, ${t('Phone')}: ${complaint.deliveryTelephone}`
                                                        : ''}
                                                </Cell>
                                            </Row>
                                        </Table>
                                    </Cell>

                                    <Cell className="flex-1">
                                        {complaint.items.map((item) => (
                                            <Table key={item.orderItem?.uuid} className="border-0 p-0">
                                                <Row>
                                                    <CellMinor>{t('Product name')}</CellMinor>
                                                    <Cell>{item.productName}</Cell>
                                                </Row>
                                                <Row>
                                                    <CellMinor>{t('Quantity')}</CellMinor>
                                                    <Cell>{item.quantity}</Cell>
                                                </Row>
                                                <Row>
                                                    <CellMinor>{t('Description')}</CellMinor>
                                                    <Cell>{item.description}</Cell>
                                                </Row>
                                            </Table>
                                        ))}
                                    </Cell>
                                </Row>
                            </Table>
                        ))}
                    </div>
                ) : (
                    <p>{t('You have no complaints')}</p>
                )}
            </Webline>

            <Webline className="mt-6">
                {newsLetterSubscriber && (
                    <>
                        <div className="h2 mb-3 mt-6">{t('Newsletter')}</div>
                        <Table
                            head={
                                <Row>
                                    <CellHead>{t('Newsletter subscription')}</CellHead>
                                    <CellHead className="text-right">{t('Subscribed from')}</CellHead>
                                </Row>
                            }
                        >
                            <Row>
                                <CellMinor>{newsLetterSubscriber.email}</CellMinor>
                                <td className="text-right">{formatDate(newsLetterSubscriber.createdAt)}</td>
                            </Row>
                        </Table>
                    </>
                )}
            </Webline>
        </>
    );
};
