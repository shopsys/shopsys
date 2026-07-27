import { act, render, screen } from '@testing-library/react';
import { YouTubeThumbnail } from 'components/Basic/YouTubeThumbnail/YouTubeThumbnail';
import { describe, expect, test, vi } from 'vitest';

const imageMocks = vi.hoisted(() => ({
    onError: undefined as (() => void) | undefined,
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt, onError, src }: { alt: string; onError?: () => void; src?: string }) => {
        imageMocks.onError = onError;

        return <span aria-label={alt} data-src={src} role="img" />;
    },
}));

describe('YouTubeThumbnail', () => {
    test('falls back to the high-quality thumbnail when the maximum resolution is unavailable', () => {
        render(<YouTubeThumbnail alt="Product video" height={80} videoId="youtube-token" width={80} />);

        const thumbnail = screen.getByRole('img', { name: 'Product video' });

        expect(thumbnail).toHaveAttribute('data-src', 'https://img.youtube.com/vi/youtube-token/maxresdefault.jpg');

        act(() => {
            imageMocks.onError?.();
        });

        expect(thumbnail).toHaveAttribute('data-src', 'https://img.youtube.com/vi/youtube-token/hqdefault.jpg');
    });
});
