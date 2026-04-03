import { CartSteps } from 'components/Blocks/CartSteps/CartSteps';
import { OrderSummary } from 'components/Blocks/OrderSummary/OrderSummary';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';

type OrderContentWrapperProps = {
    activeStep: number;
    isTransportOrPaymentLoading?: boolean;
};

export const OrderContentWrapper: FC<OrderContentWrapperProps> = ({
    activeStep,
    isTransportOrPaymentLoading,
    children,
}) => {
    const { url } = useDomainConfig();

    return (
        <>
            <CartSteps activeStep={activeStep} domainUrl={url} />

            <section className="grid vl:grid-cols-3 vl:gap-10">
                <div className="vl:col-span-2">{children}</div>

                <OrderSummary isTransportOrPaymentLoading={isTransportOrPaymentLoading} />
            </section>
        </>
    );
};
