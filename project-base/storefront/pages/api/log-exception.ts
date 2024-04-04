import { NextApiRequest, NextApiResponse } from 'next';
import { logException } from 'utils/errors/logException';

export default (req: NextApiRequest, res: NextApiResponse): void => {
    if (req.method === 'POST' && typeof req.body === 'object' && 'exception' in req.body) {
        logException({ error: req.body, location: 'log-exception API exception handler' });
    }

    res.status(200).json({ status: 'ok' });
};
