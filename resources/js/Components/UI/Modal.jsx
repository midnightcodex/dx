import React, { useEffect } from 'react';

export default function Modal({
    isOpen,
    onClose,
    title,
    children,
    size = 'md', // sm, md, lg, xl
    footer,
}) {
    // Close on escape key
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape') onClose();
        };
        if (isOpen) {
            document.addEventListener('keydown', handleEscape);
            document.body.style.overflow = 'hidden';
        }
        return () => {
            document.removeEventListener('keydown', handleEscape);
            document.body.style.overflow = '';
        };
    }, [isOpen, onClose]);

    if (!isOpen) return null;

    const widths = { sm: '400px', md: '500px', lg: '650px', xl: '800px' };

    return (
        <div
            style={{
                position: 'fixed',
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
                backgroundColor: 'rgba(0, 0, 0, 0.5)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                zIndex: 1000,
                animation: 'fadeIn 0.2s ease',
            }}
            onClick={onClose}
        >
            <div
                style={{
                    backgroundColor: 'var(--color-white)',
                    borderRadius: 'var(--radius-xl)',
                    width: '90%',
                    maxWidth: widths[size],
                    maxHeight: '90vh',
                    overflow: 'hidden',
                    display: 'flex',
                    flexDirection: 'column',
                    boxShadow: 'var(--shadow-xl)',
                    animation: 'slideUp 0.2s ease',
                }}
                onClick={(e) => e.stopPropagation()}
            >
                {/* Header */}
                <div style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    padding: '20px 24px',
                    borderBottom: '1px solid var(--color-gray-100)',
                }}>
                    <h2 style={{ margin: 0, fontSize: '18px', fontWeight: 600, color: 'var(--color-gray-900)' }}>{title}</h2>
                    <button
                        onClick={onClose}
                        style={{
                            background: 'none',
                            border: 'none',
                            fontSize: '24px',
                            cursor: 'pointer',
                            color: 'var(--color-gray-400)',
                            padding: '4px',
                            lineHeight: 1,
                        }}
                    >
                        ×
                    </button>
                </div>

                {/* Body */}
                <div style={{
                    padding: '24px',
                    overflowY: 'auto',
                    flex: 1,
                }}>
                    {children}
                </div>

                {/* Footer */}
                {footer && (
                    <div style={{
                        display: 'flex',
                        justifyContent: 'flex-end',
                        gap: '12px',
                        padding: '16px 24px',
                        borderTop: '1px solid var(--color-gray-100)',
                        backgroundColor: 'var(--color-gray-50)',
                    }}>
                        {footer}
                    </div>
                )}
            </div>
            <style>{`
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes slideUp {
                    from { transform: translateY(20px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
            `}</style>
        </div>
    );
}
