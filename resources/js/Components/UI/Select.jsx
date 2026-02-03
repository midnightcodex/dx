import React from 'react';

export default function Select({
    label,
    name,
    value,
    onChange,
    options = [],
    placeholder = 'Select...',
    required = false,
    disabled = false,
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
            <select
                id={name}
                name={name}
                value={value}
                onChange={onChange}
                required={required}
                disabled={disabled}
                style={{
                    width: '100%',
                    padding: '10px 14px',
                    borderRadius: 'var(--radius-md)',
                    border: `1px solid ${error ? 'var(--color-danger)' : 'var(--color-gray-200)'}`,
                    fontSize: '14px',
                    backgroundColor: disabled ? 'var(--color-gray-100)' : 'var(--color-white)',
                    cursor: 'pointer',
                    outline: 'none',
                }}
            >
                <option value="">{placeholder}</option>
                {options.map((opt, idx) => (
                    <option key={idx} value={opt.value}>{opt.label}</option>
                ))}
            </select>
            {error && <span style={{ fontSize: '12px', color: 'var(--color-danger)', marginTop: '4px', display: 'block' }}>{error}</span>}
        </div>
    );
}
