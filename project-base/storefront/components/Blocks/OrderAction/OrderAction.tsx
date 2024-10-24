import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Button } from 'components/Forms/Button/Button';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { TIDs } from 'cypress/tids';
import { twJoin } from 'tailwind-merge';

type OrderActionProps = {
    buttonBack: string;
    buttonNext: string;
    hasDisabledLook: boolean;
    withGapBottom?: boolean;
    withGapTop?: boolean;
    backStepClickHandler?: () => void;
    nextStepClickHandler?: () => void;
    shouldShowSpinnerOnNextStepButton?: boolean;
    shouldUseConvertim?: boolean;
};

export const OrderAction: FC<OrderActionProps> = ({
    buttonBack,
    buttonNext,
    hasDisabledLook,
    backStepClickHandler,
    nextStepClickHandler,
    withGapBottom,
    withGapTop,
    shouldShowSpinnerOnNextStepButton,
    shouldUseConvertim = false,
}) => {
    return (
        <div
            className={twJoin(
                'flex flex-col flex-wrap items-center lg:w-full lg:flex-row lg:justify-between ',
                withGapBottom && 'mb-12 lg:mb-24',
                withGapTop && 'mt-8',
            )}
        >
            <div className="order-2 lg:order-1">
                <Button tid={TIDs.blocks_orderaction_back} variant="inverted" onClick={backStepClickHandler}>
                    <ArrowIcon className={twJoin('relative top-0 mr-1 rotate-90')} />
                    {buttonBack}
                </Button>
            </div>

            <div className="order-1 mb-8 w-auto lg:order-2 lg:mb-0" tid={TIDs.blocks_orderaction_next}>
                <SubmitButton
                    data-convertim-toggle={shouldUseConvertim}
                    isWithDisabledLook={hasDisabledLook}
                    onClick={shouldUseConvertim ? undefined : nextStepClickHandler}
                >
                    {shouldShowSpinnerOnNextStepButton && <SpinnerIcon className="w-5" />}
                    <span>{buttonNext}</span>
                    <ArrowIcon className="relative top-0 ml-1 -rotate-90" />
                </SubmitButton>
            </div>
        </div>
    );
};
