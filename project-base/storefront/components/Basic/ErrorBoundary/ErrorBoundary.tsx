import { Component, ErrorInfo, ReactNode } from 'react';

export type FallbackProps = {
    error: Error;
    resetErrorBoundary: () => void;
};

type ErrorBoundaryState = {
    didCatch: boolean;
    error: Error | null;
};

type ErrorBoundaryProps = {
    children: ReactNode;
    fallbackRender: (props: FallbackProps) => ReactNode;
    onError?: (error: Error, info: ErrorInfo) => void;
};

const initialState: ErrorBoundaryState = {
    didCatch: false,
    error: null,
};

export class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
    constructor(props: ErrorBoundaryProps) {
        super(props);
        this.resetErrorBoundary = this.resetErrorBoundary.bind(this);
        this.state = initialState;
    }

    static getDerivedStateFromError(error: Error): ErrorBoundaryState {
        return {
            didCatch: true,
            error,
        };
    }

    componentDidCatch(error: Error, info: ErrorInfo): void {
        this.props.onError?.(error, info);
    }

    resetErrorBoundary(): void {
        if (this.state.error !== null) {
            this.setState(initialState);
        }
    }

    render(): ReactNode {
        const { children, fallbackRender } = this.props;
        const { didCatch, error } = this.state;

        if (didCatch && error) {
            return fallbackRender({
                error,
                resetErrorBoundary: this.resetErrorBoundary,
            });
        }

        return children;
    }
}
