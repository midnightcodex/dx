import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

export default function EmployeeCreate({ departments, users }) {
    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        employee_code: '',
        department_id: '',
        designation: '',
        date_of_joining: '',
        user_id: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('hr.employees.store'));
    };

    return (
        <MainLayout title="Onboard Employee">
            <Head title="New Employee" />

            <form onSubmit={submit} className="max-w-4xl mx-auto bg-white shadow rounded-lg p-6">
                <h3 className="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Personal Details</h3>
                <div className="grid grid-cols-2 gap-6 mb-6">
                    <Input label="First Name *" value={data.first_name} onChange={e => setData('first_name', e.target.value)} error={errors.first_name} />
                    <Input label="Last Name *" value={data.last_name} onChange={e => setData('last_name', e.target.value)} error={errors.last_name} />
                    <Input label="Email *" type="email" value={data.email} onChange={e => setData('email', e.target.value)} error={errors.email} />
                    <Input label="Employee Code *" value={data.employee_code} onChange={e => setData('employee_code', e.target.value)} error={errors.employee_code} />
                </div>

                <h3 className="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Job Details</h3>
                <div className="grid grid-cols-2 gap-6 mb-6">
                    <Select
                        label="Department *"
                        value={data.department_id}
                        onChange={e => setData('department_id', e.target.value)}
                        options={departments.map(d => ({ value: d.id, label: d.name }))}
                        error={errors.department_id}
                    />
                    <Input label="Designation *" value={data.designation} onChange={e => setData('designation', e.target.value)} error={errors.designation} />
                    <Input label="Joining Date *" type="date" value={data.date_of_joining} onChange={e => setData('date_of_joining', e.target.value)} error={errors.date_of_joining} />
                    <Select
                        label="Link System User (Optional)"
                        value={data.user_id}
                        onChange={e => setData('user_id', e.target.value)}
                        options={[{ value: '', label: 'None' }, ...users.map(u => ({ value: u.id, label: `${u.name} (${u.email})` }))]}
                        error={errors.user_id}
                    />
                </div>

                <div className="flex justify-end gap-3">
                    <Link href={route('hr.employees.index')} className="px-4 py-2 border rounded text-gray-700">Cancel</Link>
                    <button type="submit" disabled={processing} className="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">
                        Create Employee
                    </button>
                </div>
            </form>
        </MainLayout>
    );
}
