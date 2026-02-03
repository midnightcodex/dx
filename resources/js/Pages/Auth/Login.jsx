import React from 'react';
import { Link, Head, useForm } from '@inertiajs/react';
import GuestLayout from '../../Components/Layout/GuestLayout';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            <div className="stripe-auth-content">
                <h2 className="stripe-title">Sign in to your dashboard</h2>

                <form onSubmit={submit} className="stripe-form">
                    <div className="stripe-field">
                        <label className="stripe-label">Email address</label>
                        <input
                            type="email"
                            className={`stripe-input ${errors.email ? 'has-error' : ''}`}
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                            autoFocus
                        />
                        {errors.email && <span className="stripe-error-msg">{errors.email}</span>}
                    </div>

                    <div className="stripe-field">
                        <div className="stripe-label-row">
                            <label className="stripe-label">Password</label>
                            <Link href="/forgot-password" className="stripe-forgot-link">Forgot password?</Link>
                        </div>
                        <input
                            type="password"
                            className={`stripe-input ${errors.password ? 'has-error' : ''}`}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required
                        />
                        {errors.password && <span className="stripe-error-msg">{errors.password}</span>}
                    </div>

                    <div className="stripe-checkbox-row">
                        <label className="stripe-checkbox-container">
                            <input
                                type="checkbox"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                            />
                            <span className="stripe-checkmark"></span>
                            <span className="stripe-checkbox-text">Stay signed in for a week</span>
                        </label>
                    </div>

                    <button type="submit" className="stripe-btn" disabled={processing}>
                        {processing ? 'Authenticating...' : 'Sign in'}
                        <span className="stripe-arrow">→</span>
                    </button>
                </form>

                <div className="stripe-footer">
                    New to SME ERP? <Link href="/register" className="stripe-link">Create an account</Link>
                </div>
            </div>

            <style>{`
                .stripe-title {
                    font-size: 24px;
                    font-weight: 700;
                    color: #0a2540;
                    margin: 0 0 32px;
                    letter-spacing: -0.2px;
                }

                .stripe-form {
                    display: flex;
                    flex-direction: column;
                    gap: 24px;
                }

                .stripe-label {
                    display: block;
                    font-size: 14px;
                    font-weight: 500;
                    color: #425466;
                    margin-bottom: 6px;
                }

                .stripe-label-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 6px;
                }

                .stripe-input {
                    width: 100%;
                    padding: 12px 14px;
                    font-size: 16px;
                    border: 1px solid #e6ebf1;
                    border-radius: 4px; /* Slightly sharper styling */
                    background-color: #fff;
                    color: #0a2540;
                    transition: box-shadow 0.2s ease, border-color 0.2s ease;
                    outline: none;
                }

                .stripe-input:focus {
                    border-color: #aab7c4;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05), 0 0 0 4px rgba(99, 91, 255, 0.15);
                }

                .stripe-input.has-error {
                    border-color: #ef4444;
                }

                .stripe-error-msg {
                    display: block;
                    font-size: 13px;
                    color: #ef4444;
                    margin-top: 6px;
                }

                .stripe-forgot-link {
                    font-size: 14px;
                    color: #635bff;
                    text-decoration: none;
                    font-weight: 500;
                }
                
                .stripe-forgot-link:hover {
                    color: #0a2540;
                }

                .stripe-btn {
                    width: 100%;
                    padding: 12px 16px;
                    background: #635bff;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    gap: 8px;
                    transition: all 0.2s ease;
                    box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
                }

                .stripe-btn:hover:not(:disabled) {
                    background: #4d45e6;
                    transform: translateY(-1px);
                    box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
                }

                .stripe-btn:disabled {
                    opacity: 0.7;
                    cursor: not-allowed;
                }

                .stripe-arrow {
                    transition: transform 0.2s ease;
                }

                .stripe-btn:hover .stripe-arrow {
                    transform: translateX(3px);
                }

                .stripe-checkbox-row {
                    display: flex;
                    align-items: center;
                }

                .stripe-checkbox-container {
                    display: flex;
                    align-items: center;
                    cursor: pointer;
                    font-size: 14px;
                    color: #425466;
                }

                .stripe-checkbox-container input {
                    width: 16px;
                    height: 16px;
                    margin-right: 8px;
                    accent-color: #635bff;
                }

                .stripe-footer {
                    margin-top: 32px;
                    font-size: 14px;
                    color: #697386;
                }

                .stripe-link {
                    color: #635bff;
                    text-decoration: none;
                    font-weight: 500;
                }

                .stripe-link:hover {
                    text-decoration: underline;
                }
            `}</style>
        </GuestLayout>
    );
}
