import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import dynamic from 'next/dynamic';
import { ReactElement, useEffect, useEffectEvent } from 'react';
import { FieldErrors, FieldValues, UseFormReturn } from 'react-hook-form';
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
        };
    },
    overrideVisibility?: boolean,
    gtmMessageOrigin?: GtmMessageOriginType,
) => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const onShowError = useEffectEvent(() => {
        updatePortalContent(
            <ErrorPopup
                errors={formProviderMethods.formState.errors as FieldErrors}
                fields={fields}
                gtmMessageOrigin={gtmMessageOrigin}
            />,
        );
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
