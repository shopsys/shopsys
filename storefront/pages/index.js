import React from 'react';
import { useQuery } from 'urql';
import { withUrqlClient } from 'next-urql';
import { useTranslation } from 'react-i18next';
import SsfwButton from '../components/forms/SsfwButton';
import ShopsysInUserText from '../components/in/ShopsysInUserText';
import getConfig from 'next/config';
import ShopsysTextInput from '../components/forms/ShopsysTextInput';
import { FormProvider, useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as Yup from 'yup';
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

    const validationSchema = Yup.object().shape({
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

    const formProviderMethods = useForm({
        mode: 'onBlur',
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
