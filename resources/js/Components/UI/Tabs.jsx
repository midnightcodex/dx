import React, { useState } from 'react';

export default function Tabs({ tabs, defaultTab = 0, className = '' }) {
    const [activeTab, setActiveTab] = useState(defaultTab);

    return (
        <div className={`tabs ${className}`}>
            <div className="tabs-list">
                {tabs.map((tab, index) => (
                    <button
                        key={index}
                        className={`tab ${activeTab === index ? 'active' : ''}`}
                        onClick={() => setActiveTab(index)}
                    >
                        {tab.label}
                        {tab.badge && (
                            <span style={{
                                marginLeft: '8px',
                                backgroundColor: 'var(--color-primary)',
                                color: 'white',
                                padding: '2px 6px',
                                borderRadius: '10px',
                                fontSize: '10px',
                                fontWeight: '600'
                            }}>
                                {tab.badge}
                            </span>
                        )}
                    </button>
                ))}
            </div>
            <div className="tabs-content">
                {tabs[activeTab]?.content}
            </div>
        </div>
    );
}
