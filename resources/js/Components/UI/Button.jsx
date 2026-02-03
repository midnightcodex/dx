import React from 'react';

export default function Button({
    children,
    variant = 'primary',
    size = 'md',
    disabled = false,
    loading = false,
    onClick,
    className = '',
    ...props
}) {
    const classes = [
        'btn',
        `btn-${variant}`,
        size !== 'md' ? `btn-${size}` : '',
        className
    ].filter(Boolean).join(' ');

    return (
        <button
            className={classes}
            disabled={disabled || loading}
            onClick={onClick}
            {...props}
        >
            {loading ? (
                <span style={{ animation: 'spin 1s linear infinite' }}>⏳</span>
            ) : children}
        </button>
    );
}
