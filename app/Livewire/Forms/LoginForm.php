<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class LoginForm extends Form
{
    #[Validate('required|string|max:255', message: 'O campo usuário é obrigatório.')]
    public string $username = '';

    #[Validate('required|string|min:6', message: 'O campo senha é obrigatório e deve ter pelo menos 6 caracteres.')]
    public string $password = '';

    public function messages(): array
    {
        return [
            'username.required' => 'O campo usuário é obrigatório.',
            'username.string' => 'O campo usuário deve ser um texto.',
            'username.max' => 'O campo usuário não pode ter mais de 255 caracteres.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.string' => 'O campo senha deve ser um texto.',
            'password.min' => 'O campo senha deve ter pelo menos 6 caracteres.',
        ];
    }
}
