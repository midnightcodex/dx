import React from 'react';
import { Link, Head, useForm } from '@inertiajs/react';
import GuestLayout from '../../Components/Layout/GuestLayout';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        console.log('Forgot Password attempt', data);
        // post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Reset Password" />

            <div className="stripe-auth-content">
                <Link href="/login" className="stripe-back">
                    ← Back to login
                </Link>

                <h2 className="stripe-title">Reset your password</h2>
                <p className="stripe-subtitle">
                    Enter the email address associated with your account and we'll send you a link to reset your password.
                </p>

                {status && <div className="stripe-status-message">{status}</div>}

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

                    <button type="submit" className="stripe-btn" disabled={processing}>
                        {processing ? 'Sending...' : 'Send reset link'}
                    </button>
                </form>
            </div>

            <style>{`
                .stripe-title {
                    font-size: 24px;
                    font-weight: 700;
                    color: #0a2540;
                    margin: 0 0 12px;
                    letter-spacing: -0.2px;
                }

                .stripe-subtitle {
                    font-size: 15px;
                    color: #425466;
                    margin: 0 0 32px;
                    line-height: 1.5;
                }

                .stripe-back {
                    display: inline-block;
                    font-size: 14px;
                    color: #425466;
                    text-decoration: none;
                    margin-bottom: 24px;
                    font-weight: 500;
                }

                .stripe-back:hover {
                    color: #0a2540;
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

                .stripe-input {
                    width: 100%;
                    padding: 12px 14px;
                    font-size: 16px;
                    border: 1px solid #e6ebf1;
                    border-radius: 4px;
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

                .stripe-status-message {
                    padding: 12px;
                    background: #e3f9e5;
                    color: #0d5f2a;
                    border-radius: 4px;
                    margin-bottom: 24px;
                    font-size: 14px;
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
            `}</style>
        </GuestLayout>
    );
}
