import React, { useState, useEffect } from 'react';
import Sidebar from './Sidebar';
import Header from './Header';

export default function MainLayout({ children, title, subtitle }) {
    const [sidebarOpen, setSidebarOpen] = useState(window.innerWidth > 768);
    const [isMobile, setIsMobile] = useState(window.innerWidth <= 768);

    useEffect(() => {
        const handleResize = () => {
            const mobile = window.innerWidth <= 768;
            setIsMobile(mobile);
            if (!mobile) setSidebarOpen(true);
            else setSidebarOpen(false);
        };

        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    const toggleSidebar = () => setSidebarOpen(!sidebarOpen);

    return (
        <div className="dashboard-layout">
            {/* Mobile Overlay */}
            {isMobile && (
                <div
                    className={`sidebar-overlay ${sidebarOpen ? 'active' : ''}`}
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            <Sidebar isOpen={sidebarOpen} onToggle={toggleSidebar} isMobile={isMobile} />

            <main className="dashboard-main" style={{ marginLeft: isMobile ? 0 : (sidebarOpen ? '260px' : '72px') }}>
                <Header
                    title={title}
                    subtitle={subtitle}
                    onMenuClick={toggleSidebar}
                />
                <div className="dashboard-content">
                    {children}
                </div>
            </main>
        </div>
    );
}
