<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Helpers\CartManagement;

#[Title('Checkout - Bakpao Serdam')]
class CheckoutPage extends Component
{
    public $first_name;
    public $last_name;
    public $phone;
    public $street_address;
    public $city;
    public $state;
    public $zip_code;
    public $payment_method;

    public function placeOrder()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'street_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_method' => 'required',
        ]);

        dd('testing');
    }
    public function render()
    {
        $cartItems = CartManagement::getCartItemsFromCookie();
        $grandTotal = CartManagement::calculateGrandTotal($cartItems);
        return view('livewire.checkout-page', [
            'cartItems' => $cartItems,
            'grandTotal' => $grandTotal,
        ]);
        // ])->title('Checkout');
    }
}
