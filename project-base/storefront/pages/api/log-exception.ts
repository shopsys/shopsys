import { logException } from 'helpers/errors/logException';
import { NextApiRequest, NextApiResponse } from 'next';

export default (req: NextApiRequest, res: NextApiResponse): void => {
    if (req.method === 'POST' && typeof req.body === 'object' && 'exception' in req.body) {
        logException({ error: req.body, location: 'log-exception API exception handler' });
    }

    res.status(200).json({ status: 'ok' });
};
