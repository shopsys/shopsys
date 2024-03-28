import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
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
            <OrderSteps activeStep={activeStep} domainUrl={url} />

            <div className="mb-24 flex w-full flex-col flex-wrap vl:mt-7 vl:mb-16 vl:flex-row">
                <div className="mb-16 w-full vl:mb-0 vl:min-h-[61vh] vl:flex-1 vl:pr-10">{children}</div>
                <div className="w-full vl:max-w-md">
                    <OrderSummary isTransportOrPaymentLoading={isTransportOrPaymentLoading} />
                </div>
            </div>
        </>
    );
};
