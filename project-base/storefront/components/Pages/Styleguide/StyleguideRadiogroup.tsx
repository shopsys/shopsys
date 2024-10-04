import { StyleguideSection } from './StyleguideElements';
import { yupResolver } from '@hookform/resolvers/yup';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { FormProvider } from 'react-hook-form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

const getStyleguideExampleFormResolver = () =>
    yupResolver(
        Yup.object().shape<Record<keyof { country: 'cz' | 'de' | 'pl' }, any>>({
            country: Yup.string().oneOf(['cz', 'de', 'pl']),
        }),
    );

export const StyleguideRadiogroup: FC = () => {
    const formProviderMethods = useShopsysForm(getStyleguideExampleFormResolver(), {
        country: 'cz',
    });

    const formMeta = {
        formName: 'contact-information-form',
        messages: {
            error: 'Could not create order',
        },
        fields: {
            country: {
                name: 'country' as const,
                label: 'Country',
            },
        },
    };

    return (
        <StyleguideSection className="flex flex-col gap-3" title="RadioGroup">
            <FormProvider {...formProviderMethods}>
                <RadiobuttonGroup
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.country.name}
                    radiobuttons={[
                        {
                            label: 'Czechia',
                            value: 'cz',
                            disabled: true,
                        },
                        {
                            label: 'Germany',
                            value: 'de',
                        },
                        {
                            label: 'Poland',
                            value: 'pl',
                        },
                    ]}
                    render={(radiobutton, key) => (
                        <FormLine key={key} bottomGap className="w-full flex-none lg:w-1/2">
                            {radiobutton}
                        </FormLine>
                    )}
                />
            </FormProvider>
        </StyleguideSection>
    );
};
