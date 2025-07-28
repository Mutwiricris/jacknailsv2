<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_ids' => ['required', 'array', 'min:1', 'max:2'],
            'service_ids.*' => ['required', 'exists:services,id'],
            'client_name' => ['required', 'string', 'min:2', 'max:100'],
            'client_email' => ['required', 'email', 'max:100'],
            'client_phone' => ['required', 'string', 'min:10', 'max:15'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'time_slot_id' => ['nullable', 'exists:time_slots,id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::in(['cash', 'mpesa', 'card', 'bank_transfer'])],
        ];
    }

    public function messages(): array
    {
        return [
            'service_ids.required' => 'Please select at least one service.',
            'service_ids.max' => 'You can select a maximum of 2 services.',
            'service_ids.*.exists' => 'One of the selected services is not available.',
            'client_name.required' => 'Your name is required.',
            'client_name.min' => 'Name must be at least 2 characters.',
            'client_email.required' => 'Email address is required.',
            'client_email.email' => 'Please enter a valid email address.',
            'client_phone.required' => 'Phone number is required.',
            'client_phone.min' => 'Phone number must be at least 10 characters.',
            'appointment_date.required' => 'Please select an appointment date.',
            'appointment_date.after_or_equal' => 'Appointment date cannot be in the past.',
            'start_time.required' => 'Please select an appointment time.',
            'start_time.date_format' => 'Please select a valid time.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Please select a valid payment method.',
        ];
    }
}