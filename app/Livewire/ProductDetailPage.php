<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Product;
use App\Helpers\CartManagement;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class ProductDetailPage extends Component
{

    public $quantity = 1;
    public $product;

    public function increaseQty()
    {
        $this->quantity++;
    }

    public function decreaseQty()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart($productId)
    {
        CartManagement::addItemToCartWithQty(
            $productId,
            $this->quantity
        );

        LivewireAlert::title('Success')
            ->text('Added to cart successfully')
            ->position('bottom-end')
            ->timer(3000)
            ->toast()
            ->show();
    }

    public function mount(string $slug)
    {
        $this->product = Product::where('slug', $slug)
            ->where('is_active', 1)
            ->with(['category', 'brand'])
            ->firstOrFail();
    }

    #[Title('Product Detail')]
    public function render()
    {
        return view('livewire.product-detail-page');
    }
}
