import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import dynamic from 'next/dynamic';
import { ReactElement, useEffect, useEffectEvent } from 'react';
import { FieldValues, UseFormReturn } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';

const ErrorPopup = dynamic(() =>
    import('components/Blocks/Popup/ErrorPopup').then((component) => component.ErrorPopup),
);

export const useErrorPopup = <T extends FieldValues>(
    formProviderMethods: UseFormReturn<T>,
    fields: {
        [fieldName: string]: {
            name: string;
            label: string | ReactElement;
            errorMessage?: string | undefined;
        };
    },
    overrideVisibility?: boolean,
    gtmMessageOrigin?: GtmMessageOriginType,
) => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const onShowError = useEffectEvent(() => {
        updatePortalContent(<ErrorPopup fields={fields} gtmMessageOrigin={gtmMessageOrigin} />);
    });

    useEffect(() => {
        if (
            formProviderMethods.formState.isSubmitted &&
            (Object.keys(formProviderMethods.formState.errors).length > 0 || overrideVisibility)
        ) {
            onShowError();
        }
    }, [formProviderMethods.formState.isSubmitted, formProviderMethods.formState.errors, overrideVisibility]);
};
