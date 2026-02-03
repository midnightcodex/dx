import React, { useState } from 'react';
import Button from '../UI/Button';

export default function DataTable({
    columns,
    data,
    title,
    actions = true,
    onFilter,
    onSort,
    onAdd,
    filterOptions = [],
    sortOptions = []
}) {
    const [showFilterDropdown, setShowFilterDropdown] = useState(false);
    const [showSortDropdown, setShowSortDropdown] = useState(false);
    const [activeFilter, setActiveFilter] = useState('');
    const [activeSort, setActiveSort] = useState('');

    const handleFilterClick = (option) => {
        setActiveFilter(option);
        setShowFilterDropdown(false);
        if (onFilter) onFilter(option);
    };

    const handleSortClick = (option) => {
        setActiveSort(option);
        setShowSortDropdown(false);
        if (onSort) onSort(option);
    };

    const handleAddClick = () => {
        if (onAdd) onAdd();
    };

    // Default options if not provided
    const defaultFilterOptions = filterOptions.length > 0 ? filterOptions : columns.map(col => col.header);
    const defaultSortOptions = sortOptions.length > 0 ? sortOptions : columns.map(col => ({ label: col.header, value: col.accessor }));

    return (
        <div className="table-card">
            {title && (
                <div className="table-card-header">
                    <h3 className="table-card-title">{title}</h3>
                    <div className="table-card-controls" style={{ display: 'flex', gap: '8px', position: 'relative' }}>
                        {/* Filter Dropdown */}
                        <div style={{ position: 'relative' }}>
                            <Button variant="outline" size="sm" onClick={() => { setShowFilterDropdown(!showFilterDropdown); setShowSortDropdown(false); }}>
                                <span>⚙️</span> Filter {activeFilter && `(${activeFilter})`}
                            </Button>
                            {showFilterDropdown && (
                                <div style={{ position: 'absolute', top: '100%', left: 0, marginTop: '4px', backgroundColor: 'var(--color-white)', border: '1px solid var(--color-gray-200)', borderRadius: '8px', boxShadow: '0 4px 12px rgba(0,0,0,0.1)', minWidth: '150px', zIndex: 100 }}>
                                    <div style={{ padding: '8px 12px', borderBottom: '1px solid var(--color-gray-100)', fontWeight: 600, fontSize: '12px', color: 'var(--color-gray-500)' }}>Filter by</div>
                                    {defaultFilterOptions.map((option, idx) => (
                                        <div key={idx} onClick={() => handleFilterClick(option)} style={{ padding: '10px 12px', cursor: 'pointer', fontSize: '13px', borderBottom: idx < defaultFilterOptions.length - 1 ? '1px solid var(--color-gray-100)' : 'none', backgroundColor: activeFilter === option ? 'var(--color-primary-light)' : 'transparent' }}>
                                            {typeof option === 'object' ? option.label : option}
                                        </div>
                                    ))}
                                    {activeFilter && (
                                        <div onClick={() => { setActiveFilter(''); setShowFilterDropdown(false); if (onFilter) onFilter(''); }} style={{ padding: '10px 12px', cursor: 'pointer', fontSize: '13px', color: 'var(--color-danger)', borderTop: '1px solid var(--color-gray-200)' }}>
                                            ✕ Clear Filter
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>

                        {/* Sort Dropdown */}
                        <div style={{ position: 'relative' }}>
                            <Button variant="outline" size="sm" onClick={() => { setShowSortDropdown(!showSortDropdown); setShowFilterDropdown(false); }}>
                                <span>↕️</span> Sort by {activeSort && `(${activeSort})`}
                            </Button>
                            {showSortDropdown && (
                                <div style={{ position: 'absolute', top: '100%', left: 0, marginTop: '4px', backgroundColor: 'var(--color-white)', border: '1px solid var(--color-gray-200)', borderRadius: '8px', boxShadow: '0 4px 12px rgba(0,0,0,0.1)', minWidth: '150px', zIndex: 100 }}>
                                    <div style={{ padding: '8px 12px', borderBottom: '1px solid var(--color-gray-100)', fontWeight: 600, fontSize: '12px', color: 'var(--color-gray-500)' }}>Sort by</div>
                                    {defaultSortOptions.map((option, idx) => (
                                        <div key={idx} onClick={() => handleSortClick(typeof option === 'object' ? option.label : option)} style={{ padding: '10px 12px', cursor: 'pointer', fontSize: '13px', borderBottom: idx < defaultSortOptions.length - 1 ? '1px solid var(--color-gray-100)' : 'none', backgroundColor: activeSort === (typeof option === 'object' ? option.label : option) ? 'var(--color-primary-light)' : 'transparent' }}>
                                            {typeof option === 'object' ? option.label : option}
                                        </div>
                                    ))}
                                    {activeSort && (
                                        <div onClick={() => { setActiveSort(''); setShowSortDropdown(false); if (onSort) onSort(''); }} style={{ padding: '10px 12px', cursor: 'pointer', fontSize: '13px', color: 'var(--color-danger)', borderTop: '1px solid var(--color-gray-200)' }}>
                                            ✕ Clear Sort
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>

                        {/* Add Button */}
                        <Button variant="primary" size="sm" onClick={handleAddClick}>
                            + Add New
                        </Button>
                    </div>
                </div>
            )}
            <table className="data-table">
                <thead>
                    <tr>
                        {columns.map((col, idx) => (
                            <th key={idx}>{col.header}</th>
                        ))}
                        {actions && <th>Action</th>}
                    </tr>
                </thead>
                <tbody>
                    {data.map((row, rowIdx) => (
                        <tr key={rowIdx}>
                            {columns.map((col, colIdx) => (
                                <td key={colIdx}>
                                    {col.render
                                        ? col.render(row[col.accessor], row)
                                        : row[col.accessor]
                                    }
                                </td>
                            ))}
                            {actions && (
                                <td>
                                    <div className="data-table-actions">
                                        <Button variant="ghost" size="sm">
                                            Edit
                                        </Button>
                                        <Button variant="danger" size="sm">
                                            Delete
                                        </Button>
                                    </div>
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
