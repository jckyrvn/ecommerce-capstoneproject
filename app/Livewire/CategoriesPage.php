<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Category;

#[Title('Categories - Bakpao Serdam')]
class CategoriesPage extends Component
{
    public function render()
    {
        return view('livewire.categories-page', [
            'categories' => Category::where('is_active', 1)->get(),
        ]);
    }
}
