import React from 'react';
import { Link, Head, useForm } from '@inertiajs/react';
import GuestLayout from '../../Components/Layout/GuestLayout';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        console.log('Register attempt', data);
        // post(route('register'));
    };

    return (
        <GuestLayout>
            <Head title="Create Account" />

            <div className="stripe-auth-content">
                <h2 className="stripe-title">Create your account</h2>

                <form onSubmit={submit} className="stripe-form">
                    <div className="stripe-field">
                        <label className="stripe-label">Full Name</label>
                        <input
                            type="text"
                            className={`stripe-input ${errors.name ? 'has-error' : ''}`}
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                            autoFocus
                        />
                        {errors.name && <span className="stripe-error-msg">{errors.name}</span>}
                    </div>

                    <div className="stripe-field">
                        <label className="stripe-label">Email address</label>
                        <input
                            type="email"
                            className={`stripe-input ${errors.email ? 'has-error' : ''}`}
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                        />
                        {errors.email && <span className="stripe-error-msg">{errors.email}</span>}
                    </div>

                    <div className="stripe-field">
                        <label className="stripe-label">Password</label>
                        <input
                            type="password"
                            className={`stripe-input ${errors.password ? 'has-error' : ''}`}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required
                        />
                        {errors.password && <span className="stripe-error-msg">{errors.password}</span>}
                    </div>

                    <div className="stripe-field">
                        <label className="stripe-label">Confirm Password</label>
                        <input
                            type="password"
                            className={`stripe-input ${errors.password_confirmation ? 'has-error' : ''}`}
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            required
                        />
                    </div>

                    <button type="submit" className="stripe-btn" disabled={processing}>
                        {processing ? 'Creating account...' : 'Create account'}
                        <span className="stripe-arrow">→</span>
                    </button>

                    <div className="stripe-terms">
                        By clicking "Create account", you agree to our Terms of Service and Privacy Policy.
                    </div>
                </form>

                <div className="stripe-footer">
                    Already have an account? <Link href="/login" className="stripe-link">Sign in</Link>
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
                    gap: 20px;
                }

                .stripe-field {
                    /* margin-bottom handled by gap */
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
                    margin-top: 8px;
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

                .stripe-terms {
                    font-size: 12px;
                    color: #697386;
                    margin-top: 4px;
                    line-height: 1.5;
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
