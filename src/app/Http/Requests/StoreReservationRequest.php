<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'channel' => ['required', 'in:store,tokushimaru'],
            'customer_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'zip' => ['nullable', 'string', 'max:10'],
            'address1' => ['nullable', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'pickup_date' => ['nullable', 'date'],
            'pickup_time_slot' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
