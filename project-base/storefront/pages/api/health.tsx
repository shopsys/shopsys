import { NextApiRequest, NextApiResponse } from 'next';

// @ts-expect-error - Next.js API route handler
export default function handler(req: NextApiRequest, res: NextApiResponse): void {
    res.status(200).json({ status: 'ok' });
}
