import React from 'react';
import { useQuery } from "urql";
import { withUrqlClient } from 'next-urql';
import i18n from '../config/i18n';
import { I18nextProvider, useTranslation } from 'react-i18next';
import CONFIG from '../config/global';

const Index = () => {
    const { t, i18n } = useTranslation();
    
    const [result] = useQuery({
        query: `
            query categories {
                categories {
                    uuid
                    name
                }
            }
            `
    });

    function CategoryList() {
        if (result.fetching) {
            return "Loading...";
        } else if (result.error) {
            return "Oh no! Máme tam chybu ";
        } else if (result.data) {
            return (
                <>
                    <ul>
                        {result.data.categories.map(({ uuid, name }) => (
                            <li key={uuid}>{name}</li>
                        ))}
                    </ul>
                </> 
            );
        } else {
            return null;
        }
    }

    return (
        <I18nextProvider i18n={i18n}>
            <h1>{t('List of categories')}</h1>
            <CategoryList />
        </I18nextProvider>
    )
};

export default withUrqlClient((_ssrExchange, ctx) => ({
    url: CONFIG.API_URL,
}))(Index);
