import Breadcrumbs from 'components/Layout/Breadcrumbs';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import Heading from 'components/Basic/Heading';
import { HeadingWrapperStyled } from 'components/Layout/SimpleLayout/SimpleLayout.style';
import Image from 'components/Basic/Image';
import { ListedOrderType } from 'types/orders';
import Pagination from 'components/Blocks/Pagination';
import TableGrid from 'components/Basic/TableGrid';
import { TransportImageWrapperStyled } from './Orders.style';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type ListedOrdersProps = { orders: ListedOrderType[] | undefined; totalCount: number | undefined };

const Orders: FC<ListedOrdersProps> = (props) => {
    const t = useTypedTranslationFunction();
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    const { url } = useShopsysSelector((state) => state.domain);
    const [customerOrdersUrl] = useGetInternationalizedStaticUrls(['/customer/orders'], url);
    const orders = props.orders;
    return (
        <>
            <Webline>
                <HeadingWrapperStyled>
                    <Heading type="h1">{t('My orders')}</Heading>
                </HeadingWrapperStyled>
                <Breadcrumbs key="breadcrumb" breadcrumb={[{ name: t('Orders'), slug: customerOrdersUrl }]} />
            </Webline>
            <Webline>
                <TableGrid>
                    {orders !== undefined && orders.length !== 0 && (
                        <tr>
                            <th>{t('Order number')}</th>
                            <th className="text-right">{t('Creation date')}</th>
                            <th className="text-right">{t('Number of items')}</th>
                            <th>{t('Shipping')}</th>
                            <th>{t('Payment')}</th>
                            <th className="text-right">{t('Total price including VAT')}</th>
                            <th>&nbsp;</th>
                        </tr>
                    )}

                    {orders !== undefined &&
                        orders.length !== 0 &&
                        orders.map((order, index) => (
                            <tr key={index}>
                                <td>{order.number}</td>
                                <td className="text-right">{order.creationDate}</td>
                                <td className="text-right">{order.items.quantity}</td>
                                <td>
                                    <TransportImageWrapperStyled>
                                        <Image image={order.transport.image} alt={order.transport.name} />
                                    </TransportImageWrapperStyled>
                                    {order.transport.name}
                                </td>
                                <td>{order.payment}</td>
                                <td className="text-right">
                                    {formatPrice(order.totalPrice.priceWithVat, currencyCode, t)}
                                </td>
                                <td>{t('Detail')}</td>
                            </tr>
                        ))}

                    {orders === undefined && (
                        <tr>
                            <th>{t('You have no orders')}</th>
                        </tr>
                    )}
                </TableGrid>
            </Webline>
            <Webline>
                <Pagination totalCount={props.totalCount !== undefined ? props.totalCount : 0} />
            </Webline>
        </>
    );
};

export default Orders;
