import React from 'react';
import { useQuery } from "urql";
import { withUrqlClient } from 'next-urql';
import CONFIG from '../config/global';

const Index = () => {
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

    if (result.fetching) {
        return "Loading...";
    } else if (result.error) {
        return "Oh no! Máme tam chybu ";
    } else if (result.data) {
        return (
            <div>
                <ul>
                    {result.data.categories.map(({ uuid, name }) => (
                        <li key={uuid}>{name}</li>
                    ))}
                </ul>
            </div>  
        );
    } else {
        return null;
    }
};

export default withUrqlClient((_ssrExchange, ctx) => ({
    url: CONFIG.API_URL,
}))(Index);
