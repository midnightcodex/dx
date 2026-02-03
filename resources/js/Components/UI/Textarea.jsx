import React from 'react';

export default function Textarea({
    label,
    name,
    value,
    onChange,
    placeholder,
    required = false,
    disabled = false,
    rows = 4,
    error,
}) {
    return (
        <div className="form-group">
            {label && (
                <label htmlFor={name} className="form-label">
                    {label}
                    {required && <span style={{ color: 'var(--color-danger)', marginLeft: '4px' }}>*</span>}
                </label>
            )}
            <textarea
                id={name}
                name={name}
                value={value}
                onChange={onChange}
                placeholder={placeholder}
                required={required}
                disabled={disabled}
                rows={rows}
                style={{
                    width: '100%',
                    padding: '10px 14px',
                    borderRadius: 'var(--radius-md)',
                    border: `1px solid ${error ? 'var(--color-danger)' : 'var(--color-gray-200)'}`,
                    fontSize: '14px',
                    backgroundColor: disabled ? 'var(--color-gray-100)' : 'var(--color-white)',
                    resize: 'vertical',
                    outline: 'none',
                    fontFamily: 'inherit',
                }}
            />
            {error && <span style={{ fontSize: '12px', color: 'var(--color-danger)', marginTop: '4px', display: 'block' }}>{error}</span>}
        </div>
    );
}
