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
import Webline from 'components/Layout/Webline';

type OrderActionProps = {
    buttonBack: string;
    buttonNext: string;
    activeStep: number;
};

const OrderAction: FC<OrderActionProps> = (props) => {
    return (
        <Webline>
            <OrderActionStyled>
                <OrderActionLeftStyled>
                    {props.activeStep === 1 ? (
                        <NextLink href="/" passHref>
                            <OrderActionLinkBackStyled>
                                <OrderActionButtonBackIconStyled icon="Arrow" />
                                {props.buttonBack}
                            </OrderActionLinkBackStyled>
                        </NextLink>
                    ) : (
                        <OrderActionButtonBackStyled
                            type="submit"
                            borderRadius="big"
                            variant="asLink"
                            isDisabled={false}
                        >
                            <OrderActionButtonBackIconStyled icon="Arrow" />
                            {props.buttonBack}
                        </OrderActionButtonBackStyled>
                    )}
                </OrderActionLeftStyled>
                <OrderActionRightStyled>
                    <Button type="submit" borderRadius="big" variant="primary" isDisabled={false}>
                        {props.buttonNext}
                        <OrderActionButtonNextIconStyled icon="Arrow" />
                    </Button>
                </OrderActionRightStyled>
            </OrderActionStyled>
        </Webline>
    );
};

export default OrderAction;
