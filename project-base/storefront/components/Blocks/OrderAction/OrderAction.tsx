import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Button } from 'components/Forms/Button/Button';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { TIDs } from 'cypress/tids';

type OrderActionProps = {
    buttonBack: string;
    buttonNext: string;
    hasDisabledLook: boolean;
    backStepClickHandler?: () => void;
    nextStepClickHandler?: () => void;
    shouldShowSpinnerOnNextStepButton?: boolean;
};

export const OrderAction: FC<OrderActionProps> = ({
    buttonBack,
    buttonNext,
    hasDisabledLook,
    backStepClickHandler,
    nextStepClickHandler,
    shouldShowSpinnerOnNextStepButton,
}) => {
    return (
        <div className="my-5 flex flex-col-reverse items-center justify-between gap-4 md:my-10 md:flex-row">
            <Button size="large" tid={TIDs.blocks_orderaction_back} variant="inverted" onClick={backStepClickHandler}>
                <ArrowSecondaryIcon className="size-3 rotate-90" />
                {buttonBack}
            </Button>

            <SubmitButton
                isWithDisabledLook={hasDisabledLook}
                size="xlarge"
                tid={TIDs.blocks_orderaction_next}
                onClick={nextStepClickHandler}
            >
                {shouldShowSpinnerOnNextStepButton && <SpinnerIcon className="size-4" />}
                <span>{buttonNext}</span>
                <ArrowSecondaryIcon className="size-4 -rotate-90" />
            </SubmitButton>
        </div>
    );
};
