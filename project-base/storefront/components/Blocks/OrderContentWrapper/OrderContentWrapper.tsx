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

            <section className="vl:flex-row flex w-full flex-col flex-wrap">
                <div className="vl:mb-0 vl:flex-1 vl:pr-10 mb-16 w-full">{children}</div>
                <div className="vl:max-w-md w-full">
                    <OrderSummary isTransportOrPaymentLoading={isTransportOrPaymentLoading} />
                </div>
            </section>
        </>
    );
};
