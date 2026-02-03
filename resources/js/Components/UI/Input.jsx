import React from 'react';

export default function Input({
    label,
    type = 'text',
    name,
    value,
    onChange,
    placeholder,
    required = false,
    disabled = false,
    error,
    helpText,
    ...props
}) {
    return (
        <div className="form-group">
            {label && (
                <label htmlFor={name} className="form-label">
                    {label}
                    {required && <span style={{ color: 'var(--color-danger)', marginLeft: '4px' }}>*</span>}
                </label>
            )}
            <input
                type={type}
                id={name}
                name={name}
                value={value}
                onChange={onChange}
                placeholder={placeholder}
                required={required}
                disabled={disabled}
                className={`form-input ${error ? 'error' : ''}`}
                style={{
                    width: '100%',
                    padding: '10px 14px',
                    borderRadius: 'var(--radius-md)',
                    border: `1px solid ${error ? 'var(--color-danger)' : 'var(--color-gray-200)'}`,
                    fontSize: '14px',
                    backgroundColor: disabled ? 'var(--color-gray-100)' : 'var(--color-white)',
                    transition: 'border-color var(--transition-fast), box-shadow var(--transition-fast)',
                    outline: 'none',
                }}
                onFocus={(e) => {
                    e.target.style.borderColor = 'var(--color-primary)';
                    e.target.style.boxShadow = '0 0 0 3px var(--color-primary-light)';
                }}
                onBlur={(e) => {
                    e.target.style.borderColor = error ? 'var(--color-danger)' : 'var(--color-gray-200)';
                    e.target.style.boxShadow = 'none';
                }}
                {...props}
            />
            {error && <span style={{ fontSize: '12px', color: 'var(--color-danger)', marginTop: '4px', display: 'block' }}>{error}</span>}
            {helpText && !error && <span style={{ fontSize: '12px', color: 'var(--color-gray-500)', marginTop: '4px', display: 'block' }}>{helpText}</span>}
        </div>
    );
}
