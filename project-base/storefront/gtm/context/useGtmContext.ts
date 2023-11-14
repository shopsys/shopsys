import { GtmContext, GtmContextType } from './GtmProvider';
import { useContext } from 'react';

export const useGtmContext = (): GtmContextType => {
    const context = useContext(GtmContext);

    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
    if (!context) {
        throw new Error('useGtmContext must be used within a GtmProvider');
    }

    return context;
};
