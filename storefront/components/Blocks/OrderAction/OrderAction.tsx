import {
    OrderActionButtonBackIconStyled,
    OrderActionButtonBackStyled,
    OrderActionButtonNextIconStyled,
    OrderActionLeftStyled,
    OrderActionLinkBackStyled,
    OrderActionRightStyled,
    OrderActionStyled,
} from './OrderAction.style';
import Button from 'components/Forms/Button';
import { FC } from 'react';
import NextLink from 'next/link';

type OrderActionProps = {
    buttonBack: string;
    buttonNext: string;
    activeStep: number;
    isDisabled: boolean;
    withGapBottom?: boolean;
    withGapTop?: boolean;
};

const OrderAction: FC<OrderActionProps> = (props) => {
    return (
        <OrderActionStyled withGapBottom={props.withGapBottom} withGapTop={props.withGapTop}>
            <OrderActionLeftStyled>
                {props.activeStep === 1 ? (
                    <NextLink href="/" passHref>
                        <OrderActionLinkBackStyled>
                            <OrderActionButtonBackIconStyled icon="Arrow" />
                            {props.buttonBack}
                        </OrderActionLinkBackStyled>
                    </NextLink>
                ) : (
                    <>
                        <OrderActionButtonBackStyled
                            type="button"
                            borderRadius="big"
                            variant="asLink"
                            isDisabled={false}
                        >
                            <OrderActionButtonBackIconStyled icon="Arrow" />
                            {props.buttonBack}
                        </OrderActionButtonBackStyled>
                    </>
                )}
            </OrderActionLeftStyled>
            <OrderActionRightStyled>
                <Button type="submit" borderRadius="big" variant="primary" isDisabled={props.isDisabled}>
                    {props.buttonNext}
                    <OrderActionButtonNextIconStyled icon="Arrow" />
                </Button>
            </OrderActionRightStyled>
        </OrderActionStyled>
    );
};

export default OrderAction;
