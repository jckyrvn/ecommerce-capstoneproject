<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Brand;
use App\Models\Category;

#[Title('Homepage - Bakpao Serdam')]
class HomePage extends Component
{
    public function render()
    {
        return view('livewire.home-page', [
            'brands' => Brand::where('is_active', 1)->get(),
            'categories' => Category::where('is_active', 1)->get(),
        ]);
    }
}
