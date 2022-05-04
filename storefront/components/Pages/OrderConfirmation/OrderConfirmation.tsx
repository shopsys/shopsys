import { ImageWrapperStyled, MessageStyled, MessageWrapperStyled, OrderEmailStyled } from './OrderConfirmation.style';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { FC } from 'react';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import GoPayGateway from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPay';
import Heading from 'components/Basic/Heading';
import Link from 'components/Basic/Link';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { userActions } from 'redux/slices/user';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const OrderConfirmation: FC = () => {
    const testIdentifier = 'pages-orderconfirmation';

    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const { email } = useCurrentUserContactInformation();
    const { urlHash } = useShopsysSelector((state) => state.user);
    const { lastOrderUuid } = useShopsysSelector((state) => state.user);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [orderDetailUrl] = getInternationalizedStaticUrls(
        [{ url: '/order-detail/:urlHash', param: urlHash }],
        domainUrl,
    );

    useEffectOnce(() => {
        return () => {
            dispatch(userActions.setOrderUrlHash(undefined));
        };
    });

    return (
        <Webline>
            <MessageWrapperStyled data-testid={testIdentifier}>
                <ImageWrapperStyled>
                    <img alt="Objednávka odeslána" src="/public/frontend/images/sent-cart.svg" />
                </ImageWrapperStyled>
                <MessageStyled>
                    <Heading type="h1">{t('Thank you for your order')}</Heading>
                    <p>{t('We have also sent a summary to your email')}</p>
                    <OrderEmailStyled>{email}</OrderEmailStyled>
                    <Link isButton href={orderDetailUrl}>
                        {t('Go to order detail')}
                    </Link>
                    <GoPayGateway orderUuid={lastOrderUuid} />
                </MessageStyled>
            </MessageWrapperStyled>
        </Webline>
    );
};

export default OrderConfirmation;
