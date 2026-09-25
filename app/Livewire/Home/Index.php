<?php

namespace App\Livewire\Home;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.home.index', ['usuario' => Auth::user()]);
    }
}
