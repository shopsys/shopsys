import { FormLine } from 'components/Forms/Lib/FormLine';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { FormProvider } from 'react-hook-form';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import * as Yup from 'yup';
import { StyleguideSection } from './StyleguideElements';

const getStyleguideExampleFormResolver = () =>
    yupResolver<{ country: 'cz' | 'de' | 'pl' }>(
        Yup.object().shape<Record<keyof { country: 'cz' | 'de' | 'pl' }, any>>({
            country: Yup.string().oneOf(['cz', 'de', 'pl']),
        }),
    );

export const StyleguideRadiogroup: FC = () => {
    const formProviderMethods = useFormWrapper<{ country: 'cz' | 'de' | 'pl' }>(getStyleguideExampleFormResolver(), {
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
                        <FormLine key={key} className="w-full flex-none lg:w-1/2">
                            {radiobutton}
                        </FormLine>
                    )}
                />
            </FormProvider>
        </StyleguideSection>
    );
};
