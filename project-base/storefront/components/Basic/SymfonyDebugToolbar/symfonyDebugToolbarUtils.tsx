import { BatchInterceptor } from '@mswjs/interceptors';
import { XMLHttpRequestInterceptor } from '@mswjs/interceptors/XMLHttpRequest';
import { FetchInterceptor } from '@mswjs/interceptors/fetch';
import { useEffect, useState } from 'react';

export type ResponseInfo = {
    error: boolean;
    operationName: string;
    type: string;
    status: number;
    token: string;
    profiler: string;
};

type InterceptorResponseEvent = {
    response: Response;
    isMockedResponse: boolean;
    request: Request;
    requestId: string;
};

export const useRequests = (tokenHeader: string, tokenLinkHeader: string) => {
    const [responses, setResponses] = useState<ResponseInfo[]>([]);
    const addResponse = (requestInfo: ResponseInfo) => setResponses((prevState) => [...prevState, requestInfo]);

    useEffect(() => {
        const onResponse = ({ response }: InterceptorResponseEvent) => {
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

        interceptor.apply();
        interceptor.on('response', onResponse);
    }, [tokenHeader, tokenLinkHeader]);

    return {
        responses,
        reset: () => setResponses([]),
        addResponse,
    };
};

const interceptor = new BatchInterceptor({
    name: 'symfony-debug',
    interceptors: [new FetchInterceptor(), new XMLHttpRequestInterceptor()] as const,
});

const hasProfilerHeaders = (headers: Headers, tokenLinkHeader: string, tokenHeader: string) => {
    return headers.has(tokenLinkHeader) && headers.has(tokenHeader);
};
