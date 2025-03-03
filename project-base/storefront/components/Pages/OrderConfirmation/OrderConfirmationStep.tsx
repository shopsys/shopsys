import { FlowType, FlowTypeStateEnum } from './OrderConfirmationStepperFlows';
import { twJoin } from 'tailwind-merge';

type OrderConfirmationStepProps = {
    step: FlowType;
};

export const OrderConfirmationStep: FC<OrderConfirmationStepProps> = ({ step: { Icon, label, state } }) => {
    return (
        <li className="flex max-w-28 items-center justify-between self-baseline xl:max-w-none">
            <div className="flex flex-col items-center justify-start gap-2.5 xl:flex-row xl:gap-5">
                <div
                    className={twJoin(
                        'flex size-8 items-center justify-center rounded-full sm:size-11',
                        state === FlowTypeStateEnum.Active && 'bg-textAccent text-textInverted',
                        state === FlowTypeStateEnum.Inactive &&
                            'border-borderAccent bg-background text-textAccent border',
                        state === FlowTypeStateEnum.Error && 'bg-backgroundError text-textInverted',
                    )}
                >
                    <Icon className="size-4 sm:size-6" />
                </div>

                <h5
                    className={twJoin(
                        'text-center text-xs sm:text-sm lg:text-base',
                        state === FlowTypeStateEnum.Error && 'text-textError',
                    )}
                >
                    {label}
                </h5>
            </div>
        </li>
    );
};
