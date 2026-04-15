<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class UserDTO
{
    public function __construct(public ?string $name = null, public ?string $email = null, public ?string $password = null) {}

    public static function fromRequest(Request $r): self
    {
        return new self(
            $r->input('name'),
            $r->input('email'),
            $r->input('password')
        );
    }

    public function toArray(): array
    {
        $data = array_filter(
            ['name' => $this->name, 'email' => $this->email],
            fn ($v) => $v !== null
        );

        if ($this->password) {
            $data['password'] = $this->password;
        }

        return $data;
    }
}
