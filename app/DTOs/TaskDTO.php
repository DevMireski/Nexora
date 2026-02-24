<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class TaskDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?int $user_id = null,
        public readonly ?string $status = null,
        public readonly ?string $due_date = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            title:       $request->input('title'),
            description: $request->input('description'),
            user_id:     $request->input('user_id'),
            status:      $request->input('status'),
            due_date:    $request->input('due_date'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title'       => $this->title,
            'description' => $this->description,
            'user_id'     => $this->user_id,
            'status'      => $this->status,
            'due_date'    => $this->due_date,
        ], fn ($v) => $v !== null);
    }
}
