import * as Yup from 'yup';
import { FormProvider, useForm } from 'react-hook-form';
import getConfig from 'next/config';
import React from 'react';
import ShopsysCheckbox from '../components/forms/ShopsysCheckbox';
import ShopsysInUserText from '../components/in/ShopsysInUserText';
import ShopsysTextInput from '../components/forms/ShopsysTextInput';
import SsfwButton from '../components/forms/SsfwButton';
import { useQuery } from 'urql';
import { useTranslation } from 'react-i18next';
import { withUrqlClient } from 'next-urql';
import { yupResolver } from '@hookform/resolvers/yup';
const { publicRuntimeConfig } = getConfig();

const content = `<h1>Heading</h1>
<h2>Heading</h2>
<h3>Heading</h3>
<h4>Heading</h4>
<a href="#">Please click me</a>
<p>
    Lorem ipsum dolor sit amet, consectetur adipiscing elit,
    sed do eiusmod tempor incididunt ut labore et dolore
    magna aliqua. Ut enim ad minim veniam, quis nostrud
    exercitation ullamco laboris nisi ut aliquip ex ea
    commodo consequat. Duis aute irure dolor in
    reprehenderit in voluptate velit esse cillum dolore eu
    fugiat nulla pariatur. Excepteur sint occaecat cupidatat
    non proident, sunt in culpa qui officia deserunt mollit
    anim id est laborum.
</p>
<ul>
    <li>Moje položka číslo 1</li>
    <li>Moja pozycja numer 2</li>
    <li>My item number 3</li>
    <li>Moja položka číslo 4</li>
</ul>
<b>THIS IS A VERY BOLD TEXT</b>
<img src="https://picsum.photos/200" />`;

const Index = () => {
    const { t } = useTranslation();

    const [result] = useQuery({
        query: `
        query categories {
            categories {
                uuid
                name
            }
        }
        `,
    });

    const validationSchema = Yup.object().shape({
        checkboxRequired: Yup.bool().oneOf([true], t('Checkbox is required')),
        name: Yup.string()
            .notRequired()
            .matches(
                /^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð ,.'-]+$/u,
                {
                    message: t('name cannot contain special characters or numbers'),
                    excludeEmptyString: true,
                },
            ),
    });

    const formProviderMethods = useForm({
        mode: 'onBlur',
        defaultValues: {
            checkboxDefault: false,
            checkboxRequired: false,
            checkboxChecked: true,
            checkboxDisabled: false,
            checkboxDisabledChecked: true,
            checkboxWithLink: false,
        },
        criteriaMode: 'firstError',
        shouldFocusError: true,
        resolver: yupResolver(validationSchema),
    });

    function CategoryList() {
        if (result.fetching) {
            return <>{t('Loading')}...</>;
        } else if (result.error) {
            return (
                <>
                    {t('Oh no! Error')} - {result.error.message}
                </>
            );
        } else if (result.data) {
            return (
                <ul>
                    {result.data.categories.map(({ uuid, name }) => (
                        <li key={uuid}>{name}</li>
                    ))}
                </ul>
            );
        } else {
            return null;
        }
    }

    return (
        <>
            <h1>{t('List of categories')}</h1>
            <CategoryList />
            <ShopsysInUserText htmlContent={content} />
            <div>
                <SsfwButton name="button1">{t('Default')}</SsfwButton>
                <SsfwButton name="button2" additionalClassName="btn--primary">
                    {t('Primary')}
                </SsfwButton>
                <SsfwButton name="button3" additionalClassName="btn--secondary">
                    {t('Secondary')}
                </SsfwButton>
            </div>
            <FormProvider {...formProviderMethods}>
                <form style={{ marginTop: '15px' }}>
                    <ShopsysTextInput id="my-form_name" name="name" label={t('name')} shouldUseSuccess={true} />
                    <ShopsysTextInput id="my-form_password" name="password" label={t('password')} type="password" />
                    <ShopsysTextInput id="my-form_disabled" name="disabled" label={t('disabled')} disabled={true} />
                    <div style={{ width: '350px' }}>
                        <ShopsysCheckbox id="my-form_checkbox-default" name="checkboxDefault" label={t('Default')} />
                        <ShopsysCheckbox
                            id="my-form_checkbox-required"
                            name="checkboxRequired"
                            label={t('Required')}
                            required={true}
                        />
                        <ShopsysCheckbox id="my-form_checkbox-checked" name="checkboxChecked" label={t('Checked')} />
                        <ShopsysCheckbox
                            id="my-form_checkbox-disabled"
                            name="checkboxDisabled"
                            label={t('Disabled')}
                            disabled={true}
                        />
                        <ShopsysCheckbox
                            id="my-form_checkbox-disabled-checked"
                            name="checkboxDisabledChecked"
                            label={t('Disabled checked')}
                            disabled={true}
                        />
                        <ShopsysCheckbox
                            id="my-form_checkbox-with-link"
                            name="checkboxWithLink"
                            label={<a href="#">{t('this is a link')}</a>}
                        />
                    </div>
                </form>
            </FormProvider>
        </>
    );
};

export default withUrqlClient(
    () => ({
        url: publicRuntimeConfig.publicGraphqlEndpoint,
    }),
    { ssr: true },
)(Index);
