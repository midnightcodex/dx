import React, { useEffect, useState } from 'react';
import '../../styles/dashboard.css';

export default function GuestLayout({ children }) {
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
    }, []);

    return (
        <div className="stripe-auth-container">
            {/* Left Panel: Visuals */}
            <div className="stripe-visual-panel">
                <div className="stripe-mesh-gradient"></div>
                <div className="stripe-content-overlay">
                    <div className={`stripe-branding ${mounted ? 'animate-in' : ''}`}>
                        <div className="stripe-logo-badge">SME ERP</div>
                        <h1 className="stripe-headline">
                            The backbone for <br />
                            modern manufacturing.
                        </h1>
                        <div className="stripe-testimonial">
                            "Streamlined our entire inventory process in weeks."
                            <div className="stripe-author">
                                <div className="stripe-avatar"></div>
                                <span>Global Industries Inc.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="stripe-pattern-overlay"></div>
            </div>

            {/* Right Panel: Form */}
            <div className="stripe-form-panel">
                <div className={`stripe-form-wrapper ${mounted ? 'animate-in-right' : ''}`}>
                    {children}
                </div>
            </div>

            <style>{`
                :root {
                    --stripe-accent: #635bff;
                    --stripe-accent-hover: #4d45e6;
                    --stripe-text: #0a2540;
                    --stripe-text-light: #425466;
                    --stripe-input-bg: #fff;
                    --stripe-border: #e6ebf1;
                }

                body {
                    margin: 0;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
                }

                .stripe-auth-container {
                    display: flex;
                    min-height: 100vh;
                    width: 100vw;
                    overflow: hidden;
                    background: #fff;
                }

                /* Left Panel */
                .stripe-visual-panel {
                    flex: 1;
                    position: relative;
                    background-color: #0a2540;
                    overflow: hidden;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    padding: 80px;
                    color: white;
                }

                .stripe-mesh-gradient {
                    position: absolute;
                    top: -50%;
                    left: -50%;
                    width: 200%;
                    height: 200%;
                    background-image: 
                        radial-gradient(at 80% 0%, hsla(189,100%,56%,1) 0px, transparent 50%),
                        radial-gradient(at 0% 50%, hsla(355,100%,93%,1) 0px, transparent 50%),
                        radial-gradient(at 80% 50%, hsla(340,100%,76%,1) 0px, transparent 50%),
                        radial-gradient(at 0% 100%, hsla(269,100%,77%,1) 0px, transparent 50%),
                        radial-gradient(at 0% 0%, hsla(343,100%,76%,1) 0px, transparent 50%);
                    opacity: 0.6;
                    transform: skewY(-12deg);
                    animation: meshMove 20s infinite alternate ease-in-out;
                }

                @keyframes meshMove {
                    0% { transform: skewY(-12deg) translate(0, 0); }
                    100% { transform: skewY(-12deg) translate(-50px, -50px); }
                }

                .stripe-pattern-overlay {
                    position: absolute;
                    inset: 0;
                    background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                                    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
                    background-size: 40px 40px;
                    transform: skewY(-12deg) scale(1.5);
                }

                .stripe-content-overlay {
                    position: relative;
                    z-index: 10;
                    max-width: 480px;
                }

                .stripe-branding {
                    opacity: 0;
                    transform: translateY(20px);
                    transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                }

                .stripe-branding.animate-in {
                    opacity: 1;
                    transform: translateY(0);
                }

                .stripe-logo-badge {
                    display: inline-block;
                    padding: 8px 12px;
                    background: rgba(255, 255, 255, 0.1);
                    backdrop-filter: blur(10px);
                    border-radius: 100px;
                    font-size: 14px;
                    font-weight: 600;
                    margin-bottom: 32px;
                    letter-spacing: 0.5px;
                }

                .stripe-headline {
                    font-size: 48px;
                    font-weight: 700;
                    line-height: 1.1;
                    margin-bottom: 48px;
                    letter-spacing: -0.02em;
                }

                .stripe-testimonial {
                    font-size: 18px;
                    line-height: 1.5;
                    font-weight: 500;
                    border-left: 2px solid rgba(255, 255, 255, 0.3);
                    padding-left: 24px;
                    color: rgba(255, 255, 255, 0.9);
                }

                .stripe-author {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    margin-top: 16px;
                    font-size: 14px;
                    opacity: 0.8;
                }

                .stripe-avatar {
                    width: 24px;
                    height: 24px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 50%;
                }

                /* Right Panel */
                .stripe-form-panel {
                    flex: 1;
                    max-width: 600px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 60px;
                    background: #fff;
                    z-index: 20;
                }

                .stripe-form-wrapper {
                    width: 100%;
                    max-width: 380px;
                    opacity: 0;
                    transform: translateX(20px);
                    transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                    transition-delay: 0.1s;
                }

                .stripe-form-wrapper.animate-in-right {
                    opacity: 1;
                    transform: translateX(0);
                }

                @media (max-width: 960px) {
                    .stripe-visual-panel {
                        display: none;
                    }
                    .stripe-form-panel {
                        max-width: 100%;
                        background: #f7fafc;
                    }
                }
            `}</style>
        </div>
    );
}
