<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helpers\CartManagement;
use Livewire\Attributes\Title;
use App\Livewire\Partials\Navbar;

#[Title('Cart - Bakpao Serdam')]
class CartPage extends Component
{
    public $cartItems = [];
    public $grandTotal;

    public function mount()
    {
        $this->cartItems = CartManagement::getCartItemsFromCookie();
        $this->grandTotal = CartManagement::calculateGrandTotal($this->cartItems);
    }

    public function removeItem($productId)
    {
        $this->cartItems = CartManagement::removeCartItem($productId);
        $this->grandTotal = CartManagement::calculateGrandTotal($this->cartItems);

        $this->dispatch(
            'update-cart-count',
            totalCount: count($this->cartItems)
        )->to(Navbar::class);
    }
    public function increaseQty($productId)
    {
        $this->cartItems = CartManagement::incrementQuantity($productId);
        $this->grandTotal = CartManagement::calculateGrandTotal($this->cartItems);
    }
    public function decreaseQty($productId)
    {
        $this->cartItems = CartManagement::decrementQuantity($productId);
        $this->grandTotal = CartManagement::calculateGrandTotal($this->cartItems);
    }
    public function render()
    {
        return view('livewire.cart-page');
    }
}
