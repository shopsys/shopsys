import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Fragment } from 'react';
import { OrderConfirmationStep } from './OrderConfirmationStep';
import { FlowTypesEnum, useOrderConfirmationStepperFlow } from './OrderConfirmationStepperFlows';

type OrderConfirmationStepperProps = {
    flow: FlowTypesEnum;
};

export const OrderConfirmationStepper: FC<OrderConfirmationStepperProps> = ({ flow }) => {
    const flowSteps = useOrderConfirmationStepperFlow();
    const steps = flowSteps[flow];

    return (
        <ul className="mx-auto my-4 flex justify-between gap-1 rounded-xl bg-background-more p-2.5 sm:gap-2.5 sm:p-4 lg:my-10 lg:gap-6 lg:px-8 lg:py-4">
            {steps.map((step, index) => (
                <Fragment key={step.label}>
                    <OrderConfirmationStep step={step} />

                    {index < steps.length - 1 && (
                        <li className="relative flex flex-1 items-center justify-end">
                            <ArrowIcon className="absolute top-1/4 size-6 translate-x-1/2 -translate-y-1/2 -rotate-90 text-border-less sm:top-1/2" />
                            <div className="absolute inset-0 top-1/2 left-1/2 ml-0.5 h-0.5 w-full -translate-x-1/2 -translate-y-1/2 rounded-full bg-border-less" />
                        </li>
                    )}
                </Fragment>
            ))}
        </ul>
    );
};
