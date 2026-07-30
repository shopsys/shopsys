import { RenderOptions, render } from '@testing-library/react';
import { TooltipProvider } from 'components/Basic/Tooltip/Tooltip';
import { ReactElement } from 'react';

export const renderWithTooltipProvider = (element: ReactElement, options?: Omit<RenderOptions, 'wrapper'>) =>
    render(element, { ...options, wrapper: TooltipProvider });
