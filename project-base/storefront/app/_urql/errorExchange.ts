import { Translate } from 'types/translation';
import { Exchange } from 'urql';
import { pipe, tap } from 'wonka';

export const getErrorExchange =
    (t: Translate): Exchange =>
    ({ forward }) => {
        return (operations$) => {
            return pipe(
                operations$,
                forward,
                tap(({ error }) => {
                    if (error) {
                        // eslint-disable-next-line no-console
                        //console.error('🚀 -> file: errorExchange.ts:15 -> tap -> error:', error, t('Unknown error.'));
                    }
                }),
            );
        };
    };
