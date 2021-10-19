import {
    OrderActionButtonBackIconStyled,
    OrderActionButtonNextIconStyled,
    OrderActionLeftStyled,
    OrderActionLinkBackStyled,
    OrderActionRightStyled,
    OrderActionStyled,
} from './OrderAction.style';
import Button from 'components/Forms/Button';
import { FC } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/router';

type OrderActionProps = {
    buttonBack: string;
    buttonNext: string;
    buttonBackLink: string;
    buttonNextLink?: string;
    activeStep: number;
    isDisabled: boolean;
    withGapBottom?: boolean;
    withGapTop?: boolean;
};

const OrderAction: FC<OrderActionProps> = (props) => {
    const router = useRouter();

    const onNextStepHandler = () => {
        if (props.buttonNextLink !== undefined) {
            router.push(props.buttonNextLink);
        }
    };

    return (
        <OrderActionStyled withGapBottom={props.withGapBottom} withGapTop={props.withGapTop}>
            <OrderActionLeftStyled>
                <NextLink href={props.buttonBackLink} passHref>
                    <OrderActionLinkBackStyled>
                        <OrderActionButtonBackIconStyled icon="Arrow" />
                        {props.buttonBack}
                    </OrderActionLinkBackStyled>
                </NextLink>
            </OrderActionLeftStyled>
            <OrderActionRightStyled>
                <Button
                    type="submit"
                    borderRadius="big"
                    variant="primary"
                    isDisabled={props.isDisabled}
                    onClick={onNextStepHandler}
                >
                    {props.buttonNext}
                    <OrderActionButtonNextIconStyled icon="Arrow" />
                </Button>
            </OrderActionRightStyled>
        </OrderActionStyled>
    );
};

export default OrderAction;
