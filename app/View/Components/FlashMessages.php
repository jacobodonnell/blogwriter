<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class FlashMessages extends Component
{
    public readonly ?string $success;

    public readonly ?string $error;

    public function __construct(?string $success = null, ?string $error = null)
    {
        $this->success = $success ?? session('success');
        $this->error = $error ?? session('error');
    }

    public function render(): View
    {
        return view('components.flash-messages');
    }
}
