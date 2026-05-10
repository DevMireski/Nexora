<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CalendarDTO
{
    public function __construct(
        public readonly ?string $google_calendar_id = null,
        public readonly ?int $user_id = null,
    ) {}

    public static function fromRequest(Request $r): self
    {
        return new self(
            google_calendar_id: $r->input('google_calendar_id'),
            user_id: (int) $r->input('user_id'),
        );
    }

    public function toArray(): array
    {
        return array_filter(
            ['google_calendar_id' => $this->google_calendar_id],
            fn ($v) => $v !== null
        );
    }
}
