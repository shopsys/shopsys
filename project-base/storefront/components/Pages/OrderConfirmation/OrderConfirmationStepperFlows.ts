import { CheckboxCheckedIcon } from 'components/Basic/Icon/CheckboxCheckedIcon';
import { PackageDeliveredIcon } from 'components/Basic/Icon/PackageDeliveredIcon';
import { TruckClockIcon } from 'components/Basic/Icon/TruckClockIcon';
import { WalletIcon } from 'components/Basic/Icon/WalletIcon';
import useTranslation from 'next-translate/useTranslation';

export enum FlowTypesEnum {
    PaymentAwaiting = 'paymentAwaiting',
    PaymentSuccess = 'paymentSuccess',
    PaymentFailed = 'paymentFailed',
    PaymentInProcess = 'paymentInProcess',
}

export enum FlowTypeStateEnum {
    Active = 'active',
    Inactive = 'inactive',
    Error = 'error',
}

export type FlowType = {
    Icon: React.ElementType;
    label: string;
    state: FlowTypeStateEnum;
};

export const useOrderConfirmationStepperFlow = () => {
    const { t } = useTranslation();

    const paymentAwaiting: FlowType[] = [
        {
            Icon: CheckboxCheckedIcon,
            label: t('Order received'),
            state: FlowTypeStateEnum.Active,
        },
        {
            Icon: WalletIcon,
            label: t('Waiting for payment'),
            state: FlowTypeStateEnum.Inactive,
        },
        {
            Icon: TruckClockIcon,
            label: t('Order is on the way'),
            state: FlowTypeStateEnum.Inactive,
        },
        {
            Icon: PackageDeliveredIcon,
            label: t('Delivered to you'),
            state: FlowTypeStateEnum.Inactive,
        },
    ];

    const paymentSuccess: FlowType[] = [
        {
            Icon: CheckboxCheckedIcon,
            label: t('Order received'),
            state: FlowTypeStateEnum.Active,
        },
        {
            Icon: WalletIcon,
            label: t('Payment successful'),
            state: FlowTypeStateEnum.Active,
        },
        {
            Icon: TruckClockIcon,
            label: t('Order is on the way'),
            state: FlowTypeStateEnum.Inactive,
        },
        {
            Icon: PackageDeliveredIcon,
            label: t('Delivered to you'),
            state: FlowTypeStateEnum.Inactive,
        },
    ];

    const paymentFailed: FlowType[] = [
        {
            Icon: CheckboxCheckedIcon,
            label: t('Order received'),
            state: FlowTypeStateEnum.Active,
        },
        {
            Icon: WalletIcon,
            label: t('Payment failed'),
            state: FlowTypeStateEnum.Error,
        },
        {
            Icon: TruckClockIcon,
            label: t('Order is on the way'),
            state: FlowTypeStateEnum.Inactive,
        },
        {
            Icon: PackageDeliveredIcon,
            label: t('Delivered to you'),
            state: FlowTypeStateEnum.Inactive,
        },
    ];

    const paymentInProcess: FlowType[] = [
        {
            Icon: CheckboxCheckedIcon,
            label: t('Order received'),
            state: FlowTypeStateEnum.Active,
        },
        {
            Icon: WalletIcon,
            label: t('Waiting for payment confirmation'),
            state: FlowTypeStateEnum.Active,
        },
        {
            Icon: TruckClockIcon,
            label: t('Order is on the way'),
            state: FlowTypeStateEnum.Inactive,
        },
        {
            Icon: PackageDeliveredIcon,
            label: t('Delivered to you'),
            state: FlowTypeStateEnum.Inactive,
        },
    ];

    return {
        paymentAwaiting,
        paymentSuccess,
        paymentFailed,
        paymentInProcess,
    };
};
