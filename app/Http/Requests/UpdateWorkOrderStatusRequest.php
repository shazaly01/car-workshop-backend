<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    // قائمة بكل الحالات المسموح بها في النظام
    $allowedStatuses = [
        'pending_diagnosis', 'diagnosing', 'pending_quote_approval',
        'quote_approved', 'quote_rejected', 'in_progress',
        'pending_parts', 'quality_check', 'ready_for_delivery',
        'completed', 'cancelled'
    ];

    return [
        'status' => 'required|string|in:' . implode(',', $allowedStatuses),
    ];
}

}
