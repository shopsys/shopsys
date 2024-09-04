import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Button } from 'components/Forms/Button/Button';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

const Feedback: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const updateUserSnapState = usePersistStore((s) => s.updateUserSnapState);
    const isUserSnapEnabled = usePersistStore((s) => s.isUserSnapEnabled);
    const router = useRouter();
    const [isLoadingOverlayVisible, setIsLoadingOverlayVisible] = useState(false);

    const handleTurnOffUserSnap = () => {
        if (isUserSnapEnabled) {
            setIsLoadingOverlayVisible(true);
            showSuccessMessage(t('The feedback tool has been successfully deactivated for you'));
            updateUserSnapState({ isUserSnapEnabled: false });
            setTimeout(() => {
                router.reload();
            }, 2000);

            return;
        }
        showSuccessMessage(t('The feedback reporting tool has been successfully activated for you'));
        updateUserSnapState({ isUserSnapEnabled: true });
    };

    return (
        <CommonLayout title={t('Reporting feedback')}>
            <Webline className="mt-6">
                <h1 className="mb-4">{t('Reporting feedback')}</h1>
                <Button className="w-fit" onClick={handleTurnOffUserSnap}>
                    {isUserSnapEnabled
                        ? t('Turn off the feedback tool for you')
                        : t('Turn on the feedback tool for you')}
                </Button>
            </Webline>
            {isLoadingOverlayVisible && <LoaderWithOverlay className="w-8" />}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default Feedback;
