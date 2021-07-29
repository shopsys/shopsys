import * as Yup from 'yup';
import { cacheExchange, dedupExchange, fetchExchange, ssrExchange } from 'urql';
import { FC, useState } from 'react';
import { FormProvider, SubmitHandler, useForm } from 'react-hook-form';
import { initUrqlClient, withUrqlClient } from 'next-urql';
import getConfig from 'next/config';
import { GetServerSideProps } from 'next';
import PromotedCategories from '../components/blocks/categories/PromotedCategories/PromotedCategories';
import { promotedCategoriesQuery } from '../connectors/categories/PromotedCategories';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import ShopsysButton from 'components/forms/ShopsysButton';
import ShopsysCheckbox from 'components/forms/ShopsysCheckbox';
import ShopsysInUserText from 'components/in/ShopsysInUserText';
import ShopsysTextInput from 'components/forms/ShopsysTextInput';
import { useTranslation } from 'react-i18next';
import { yupResolver } from '@hookform/resolvers/yup';

type FormValues = {
    htmlContent: string;
    formConsent: boolean;
};

const { publicRuntimeConfig } = getConfig();

const Index: FC = () => {
    const { t } = useTranslation();
    const [rawHtml, setRawHtml] = useState<string | undefined>(undefined);
    const formProviderMethods = useForm<FormValues>({
        mode: 'all',
        reValidateMode: 'onChange',
        criteriaMode: 'firstError',
        resolver: yupResolver(
            Yup.object().shape({
                htmlContent: Yup.string().required(t('HTML content field is required')),
                formConsent: Yup.bool().oneOf([true], t('You have to agree with sharing your HTML content')),
            }),
        ),
    });

    const formSubmitHandler: SubmitHandler<FormValues> = ({ htmlContent }) => {
        setRawHtml(htmlContent);
    };

    return (
        <div
            style={{ width: '100vw', height: '100vh', display: 'flex', justifyContent: 'center', alignItems: 'center' }}
        >
            <div
                style={{
                    width: '50%',
                    padding: '20px',
                    boxShadow: '0 5px 10px rgba(25,84,63,0.3)',
                    borderRadius: '10px',
                }}
            >
                <h2>{t('Promoted categories')}</h2>
                <PromotedCategories />
                <div>
                    <ShopsysButton
                        style={{ margin: '5px' }}
                        size="small"
                        variant="secondary"
                        name="button1"
                        onClick={() => {
                            formProviderMethods.setValue(
                                'htmlContent',
                                `${formProviderMethods.getValues('htmlContent')} <h1>${t('Hello, world!')}</h1>`,
                                {
                                    shouldDirty: true,
                                },
                            );
                        }}
                    >
                        {`<h1>${t('Hello, world!')}</h1>`}
                    </ShopsysButton>
                    <ShopsysButton
                        style={{ margin: '5px' }}
                        size="small"
                        variant="secondary"
                        name="button2"
                        onClick={() => {
                            formProviderMethods.setValue(
                                'htmlContent',
                                `${formProviderMethods.getValues(
                                    'htmlContent',
                                )} <img src="https://picsum.photos/200/300" />`,
                                {
                                    shouldDirty: true,
                                },
                            );
                        }}
                    >
                        {'<img src="https://picsum.photos/200/300" />'}
                    </ShopsysButton>
                    <ShopsysButton
                        style={{ margin: '5px' }}
                        size="small"
                        variant="secondary"
                        name="button3"
                        onClick={() => {
                            formProviderMethods.setValue(
                                'htmlContent',
                                `${formProviderMethods.getValues('htmlContent')} <p>${t('Just some random text')}</p>`,
                                {
                                    shouldDirty: true,
                                },
                            );
                        }}
                    >
                        {`<p>${t('Just some random text')}</p>`}
                    </ShopsysButton>
                </div>
                <FormProvider {...formProviderMethods}>
                    <form style={{ marginTop: '15px' }} onSubmit={formProviderMethods.handleSubmit(formSubmitHandler)}>
                        <ShopsysTextInput
                            id="my_form-html_content"
                            name="htmlContent"
                            label={t('HTML content')}
                            shouldUseSuccess={true}
                        />
                        <ShopsysCheckbox
                            name="formConsent"
                            id="my_form-consent"
                            label={t('Do you agree with sharing your HTML content')}
                        />
                        <ShopsysButton type="submit" variant="primary" name="button3">
                            {t('Render raw HTML')}
                        </ShopsysButton>
                    </form>
                </FormProvider>
                {rawHtml && (
                    <div style={{ marginTop: '30px' }}>
                        <ShopsysInUserText htmlContent={rawHtml} />
                    </div>
                )}
            </div>
        </div>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    const ssrCache = ssrExchange({ isClient: false });
    const client = initUrqlClient(
        {
            url: publicRuntimeConfig.publicGraphqlEndpoint,
            exchanges: [dedupExchange, cacheExchange, ssrCache, fetchExchange],
        },
        false,
    );

    let serversideTranslationConfig;

    if (context.defaultLocale !== undefined && client !== null) {
        serversideTranslationConfig = await serverSideTranslations(context.defaultLocale);
        await client.query(promotedCategoriesQuery).toPromise();

        return {
            props: {
                ...serversideTranslationConfig,
                urqlState: ssrCache.extractData(),
            },
        };
    } else {
        return { props: {} };
    }
};

export default withUrqlClient(
    () => ({
        url: publicRuntimeConfig.publicGraphqlEndpoint,
    }),
    { ssr: false },
)(Index);
