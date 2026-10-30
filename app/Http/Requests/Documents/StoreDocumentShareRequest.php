<?php

namespace App\Http\Requests\Documents;

use App\Enums\ShareRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email', Rule::notIn([$this->user()->email])],
            'role' => ['required', Rule::enum(ShareRole::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.exists' => 'No account uses that email. Ask them to register first.',
            'email.not_in' => 'You already own this document.',
        ];
    }
}
