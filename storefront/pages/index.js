import React from 'react';
import { useQuery } from 'urql';
import { withUrqlClient } from 'next-urql';
import { useTranslation } from 'react-i18next';
import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();

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
        </>
    );
};

export default withUrqlClient(
    () => ({
        url: publicRuntimeConfig.publicGraphqlEndpoint,
    }),
    { ssr: true },
)(Index);
