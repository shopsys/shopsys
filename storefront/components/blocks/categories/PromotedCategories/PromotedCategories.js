import { mapPromotedCategories, promotedCategoriesQueryObject } from '../../../../connectors/categories/PromotedCategories';
import React from 'react';
import { useFetchQuery } from '../../../../hooks/UseFetchQuery';
import { useTranslation } from 'react-i18next';

export default function PromotedCategories() {
    const { t } = useTranslation();
    const result = useFetchQuery({ query: promotedCategoriesQueryObject });

    if (result.fetching) {
        return <>{t('Loading')}...</>;
    }

    const promotedCategories = mapPromotedCategories(result?.data);
    if (promotedCategories) {
        return (
            <ul>
                {promotedCategories.map(({ uuid, name }) => (
                    <li key={uuid}>{name}</li>
                ))}
            </ul>
        );
    }

    return null;
}
