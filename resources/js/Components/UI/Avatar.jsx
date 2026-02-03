import React from 'react';

export default function Avatar({
    src,
    alt = '',
    size = 'md',
    initials,
    className = ''
}) {
    const sizeClass = `avatar-${size}`;

    if (src) {
        return (
            <div className={`avatar ${sizeClass} ${className}`}>
                <img src={src} alt={alt} />
            </div>
        );
    }

    // Show initials if no image
    const displayInitials = initials || (alt ? alt.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : '?');

    return (
        <div className={`avatar ${sizeClass} ${className}`}>
            {displayInitials}
        </div>
    );
}
