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
                        state === FlowTypeStateEnum.Active && 'bg-text-accent text-text-inverted',
                        state === FlowTypeStateEnum.Inactive &&
                            'border-border-default bg-background-default text-text-accent border',
                        state === FlowTypeStateEnum.Error && 'bg-background-error text-text-inverted',
                    )}
                >
                    <Icon className="size-4 sm:size-6" />
                </div>

                <span
                    className={twJoin(
                        'h5 text-center text-xs sm:text-sm lg:text-base',
                        state === FlowTypeStateEnum.Error && 'text-text-error',
                    )}
                >
                    {label}
                </span>
            </div>
        </li>
    );
};
