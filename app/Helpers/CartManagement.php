<?php

namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Cookie;

class CartManagement
{
    public static function addCartItemsToCookie($cartItems)
    {
        Cookie::queue(
            'cart_items',
            json_encode($cartItems),
            60 * 24 * 30
        );
    }

    public static function clearCartItems()
    {
        Cookie::queue(Cookie::forget('cart_items'));
    }

    public static function getCartItemsFromCookie()
    {
        $cartItems = json_decode(
            Cookie::get('cart_items'),
            true
        );

        return $cartItems ?? [];
    }

    public static function addItemToCart($productId)
    {
        $cartItems = self::getCartItemsFromCookie();
        $existingItem = null;

        foreach ($cartItems as $key => $item) {
            if ($item['product_id'] == $productId) {
                $existingItem = $key;
                break;
            }
        }

        if ($existingItem !== null) {
            $cartItems[$existingItem]['quantity']++;
            $cartItems[$existingItem]['total_amount'] =
                $cartItems[$existingItem]['quantity'] *
                $cartItems[$existingItem]['unit_amount'];
        } else {
            $product = Product::where('id', $productId)
                ->select('id', 'name', 'price', 'images')
                ->first();

            if ($product) {
                $cartItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->images[0],
                    'quantity' => 1,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price,
                ];
            }
        }

        self::addCartItemsToCookie($cartItems);

        return count($cartItems);
    }

    public static function addItemToCartWithQty($productId, $qty = 1)
    {
        $cartItems = self::getCartItemsFromCookie();
        $existingItem = null;

        foreach ($cartItems as $key => $item) {
            if ($item['product_id'] == $productId) {
                $existingItem = $key;
                break;
            }
        }

        if ($existingItem !== null) {
            $cartItems[$existingItem]['quantity'] = $qty + $item['quantity'];
            $cartItems[$existingItem]['total_amount'] =
                $cartItems[$existingItem]['quantity'] *
                $cartItems[$existingItem]['unit_amount'];
        } else {
            $product = Product::where('id', $productId)
                ->select('id', 'name', 'price', 'images')
                ->first();

            if ($product) {
                $cartItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->images[0],
                    'quantity' => $qty,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price * $qty,
                ];
            }
        }

        self::addCartItemsToCookie($cartItems);

        return count($cartItems);
    }

    public static function removeCartItem($productId)
    {
        $cartItems = self::getCartItemsFromCookie();

        foreach ($cartItems as $key => $item) {
            if ($item['product_id'] == $productId) {
                unset($cartItems[$key]);
            }
        }

        self::addCartItemsToCookie($cartItems);
        return $cartItems;
    }

    public static function incrementQuantity($productId)
    {
        $cartItems = self::getCartItemsFromCookie();

        foreach ($cartItems as $key => $item) {
            if ($item['product_id'] == $productId) {
                $cartItems[$key]['quantity']++;
                $cartItems[$key]['total_amount'] =
                    $cartItems[$key]['quantity'] *
                    $cartItems[$key]['unit_amount'];
            }
        }

        self::addCartItemsToCookie($cartItems);
        return $cartItems;
    }

    public static function decrementQuantity($productId)
    {
        $cartItems = self::getCartItemsFromCookie();

        foreach ($cartItems as $key => $item) {
            if ($item['product_id'] == $productId && $item['quantity'] > 1) {
                $cartItems[$key]['quantity']--;
                $cartItems[$key]['total_amount'] =
                    $cartItems[$key]['quantity'] *
                    $cartItems[$key]['unit_amount'];
            }
        }

        self::addCartItemsToCookie($cartItems);
        return $cartItems;
    }

    public static function calculateGrandTotal($items)
    {
        return array_sum(
            array_column($items, 'total_amount')
        );
    }
}