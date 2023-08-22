import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
import { OrderSummary } from 'components/Blocks/OrderSummary/OrderSummary';
import { Header } from 'components/Layout/Header/Header';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';

type OrderLayoutProps = {
    activeStep: number;
    isTransportOrPaymentLoading?: boolean;
};

export const OrderLayout: FC<OrderLayoutProps> = ({ activeStep, isTransportOrPaymentLoading, children }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();

    return (
        <>
            <SeoMeta defaultTitle={t('Order')} />
            <NotificationBars />
            <Webline type="colored" className="relative mb-8">
                <Header simpleHeader />
            </Webline>
            <Adverts positionName="header" withGapBottom withWebline />
            <Webline>
                <OrderSteps activeStep={activeStep} domainUrl={url} />
            </Webline>
            <Webline>
                <div className="mb-24 flex w-full flex-col flex-wrap vl:mt-7 vl:mb-16 vl:flex-row">
                    <div className="mb-16 w-full vl:mb-0 vl:min-h-[61vh] vl:flex-1 vl:pr-10">{children}</div>
                    <div className="w-full vl:max-w-md">
                        <OrderSummary isTransportOrPaymentLoading={isTransportOrPaymentLoading} />
                    </div>
                </div>
            </Webline>
            <Adverts positionName="footer" withGapBottom withWebline />
        </>
    );
};
