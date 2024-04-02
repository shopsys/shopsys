import { BatchInterceptor } from '@mswjs/interceptors';
import browserInterceptors from '@mswjs/interceptors/lib/presets/browser';
import { useEffect, useState } from 'react';

export type ResponseInfo = {
    error: boolean;
    operationName: string;
    type: string;
    status: number;
    token: string;
    profiler: string;
};

export const useRequests = (tokenHeader: string, tokenLinkHeader: string) => {
    const [responses, setResponses] = useState<ResponseInfo[]>([]);

    const onResponse = (response: Response) => {
        const headers = response.headers;
        if (hasProfilerHeaders(headers, tokenLinkHeader, tokenHeader)) {
            const requestInfo: ResponseInfo = {
                error: false,
                operationName: response.url.split('graphql/')[1],
                type: response.type,
                status: response.status,
                token: headers.get(tokenHeader) ?? '',
                profiler: headers.get(tokenLinkHeader) ?? '',
            };

            addResponse(requestInfo);
        }
    };

    useEffect(() => {
        interceptor.apply();
        interceptor.on('response', onResponse);
    }, []);

    const addResponse = (requestInfo: ResponseInfo) => setResponses((prevState) => [...prevState, requestInfo]);

    return {
        responses,
        reset: () => setResponses([]),
        addResponse,
    };
};

const interceptor = new BatchInterceptor({
    name: 'symfony-debug',
    interceptors: browserInterceptors,
});

const hasProfilerHeaders = (headers: Headers, tokenLinkHeader: string, tokenHeader: string) => {
    return headers.has(tokenLinkHeader) && headers.has(tokenHeader);
};
