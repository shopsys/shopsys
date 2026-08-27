import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderWithdrawalConfirmationContent } from 'components/Pages/OrderWithdrawal/OrderWithdrawalConfirmationContent';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

const OrderWithdrawalConfirmationPage: FC = () => {
    const { t } = useTranslation();

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout pageTypeOverride="order-withdrawal-confirmation" title={t('Withdrawal request confirmation')}>
                <OrderWithdrawalConfirmationContent />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    if (typeof context.params?.hash !== 'string') {
        return {
            redirect: {
                destination: getBasePathWithLocale('/', context),
                statusCode: 301,
            },
        };
    }

    return initServerSideProps({
        context,
        redisClient,
        domainConfig,
        t,
        authenticationConfig: {
            authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
        },
    });
});

export default OrderWithdrawalConfirmationPage;
