<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Livewire\Attributes\Url;
use App\Helpers\CartManagement;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use App\Livewire\Partials\Navbar;


#[Title('Products - Bakpao Serdam')]
class ProductsPage extends Component
{
    use WithPagination;


    #[Url]
    public array $selected_categories = [];

    #[Url]
    public array $selectedBrands = [];

    #[Url]
    public $featured = null;

    #[Url]
    public $onSale = null;

    #[Url]
    public $price_range = 300000;

    #[Url]
    public $sort = 'latest';


    public function addToCart($productId)
    {
        $totalCount = CartManagement::addItemToCart($productId);

        $this->dispatch(
            'update-cart-count',
            totalCount: $totalCount
        )->to(Navbar::class);
        LivewireAlert::title('Success')
            ->text('Added to cart successfully')
            ->position('bottom-end')
            ->timer(3000)
            ->toast()
            ->show();
        // $this->alert('success', 'Product added to cart successfully', [
        //     'toast' => true,
        //     'position' => 'bottom-end'
        // ]);
    }
    public function render()
    {
        $productQuery = Product::query()->where('is_active', 1);

        if (!empty($this->selected_categories)) {
            $productQuery->whereIn('category_id', $this->selected_categories);
        }

        if (!empty($this->selectedBrands)) {
            $productQuery->whereIn('brand_id', $this->selectedBrands);
        }

        if ($this->featured) {
            $productQuery->where('is_featured', 1);
        }

        if ($this->onSale) {
            $productQuery->where('on_sale', 1);
        }

        if ($this->price_range) {
            $productQuery->whereBetween('price', [0, $this->price_range]);
        }

        if ($this->sort === 'latest') {
            $productQuery->latest();
        }

        if ($this->sort === 'price') {
            $productQuery->orderBy('price', 'asc');
        }

        return view('livewire.products-page', [
            'products' => $productQuery->paginate(9),
            'brands' => Brand::where('is_active', 1)->get(['id', 'name', 'slug']),
            'categories' => Category::where('is_active', 1)->get(['id', 'name', 'slug']),
        ]);
    }
}
