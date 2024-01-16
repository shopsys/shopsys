import { ResponseInfo } from './types';
import { BatchInterceptor } from '@mswjs/interceptors';
import browserInterceptors from '@mswjs/interceptors/lib/presets/browser';
import { useEffect, useState } from 'react';

export const useRequests = (tokenHeader: string, tokenLinkHeader: string) => {
    const [responses, setResponses] = useState<ResponseInfo[]>([]);

    const onResponse = (response: Response) => {
        const headers = response.headers;
        if (hasProfilerHeaders(headers, tokenLinkHeader, tokenHeader)) {
            const requestInfo: ResponseInfo = {
                error: false,
                url: response.url,
                method: '?',
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
    }, [onResponse]);

    const addResponse = (requestInfo: ResponseInfo) => setResponses((prevState) => [requestInfo, ...prevState]);

    return {
        responses,
        reset: () => setResponses([]),
        addResponse,
        hasResponses: !!responses.length,
    };
};

const interceptor = new BatchInterceptor({
    name: 'symfony-debug',
    interceptors: browserInterceptors,
});

const hasProfilerHeaders = (headers: Headers, tokenLinkHeader: string, tokenHeader: string) => {
    return headers.has(tokenLinkHeader) && headers.has(tokenHeader);
};
