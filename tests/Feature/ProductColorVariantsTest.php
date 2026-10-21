<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class ProductColorVariantsTest extends TestCase
{
    public function test_creates_product_variants_and_computes_aggregate_counts(): void
    {
        $product = Product::create([
            'name' => 'Gaming Mouse Pro',
            'slug' => 'gaming-mouse-pro-'.uniqid(),
            'price' => 3000.00,
            'sale_price' => 2800.00,
            'quantity' => 23,
            'show_quantity' => 23,
            'inventory_stock' => 23,
            'type' => 'variable',
            'condition' => 'Brand New',
        ]);

        $red = $product->variants()->create([
            'color_name' => 'Red',
            'variant_name' => 'Gaming Mouse Pro - Red',
            'attributes' => ['color' => 'Red'],
            'condition' => 'Brand New',
            'inventory_stock' => 10,
            'show_quantity' => 10,
            'stock' => 10,
            'price' => 3000.00,
            'sale_price' => 2800.00,
            'discount_percentage' => 7,
        ]);

        $white = $product->variants()->create([
            'color_name' => 'White',
            'variant_name' => 'Gaming Mouse Pro - White',
            'attributes' => ['color' => 'White'],
            'condition' => 'Brand New',
            'inventory_stock' => 8,
            'show_quantity' => 8,
            'stock' => 8,
            'price' => 3200.00,
            'sale_price' => 2900.00,
            'discount_percentage' => 9,
        ]);

        $black = $product->variants()->create([
            'color_name' => 'Black',
            'variant_name' => 'Gaming Mouse Pro - Black',
            'attributes' => ['color' => 'Black'],
            'condition' => 'Brand New',
            'inventory_stock' => 5,
            'show_quantity' => 5,
            'stock' => 5,
            'price' => 3000.00,
            'sale_price' => 2800.00,
            'discount_percentage' => 7,
        ]);

        $this->assertTrue($product->hasColorVariants());
        $this->assertEquals(3, $product->variants()->count());
        $this->assertEquals(23, (int) $product->variants()->sum('show_quantity'));
    }

    public function test_decrements_color_variant_stock_and_parent_quantity_upon_checkout(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Mechanical Keyboard RGB',
            'slug' => 'mech-keyboard-rgb-'.uniqid(),
            'price' => 5000.00,
            'sale_price' => 4500.00,
            'quantity' => 15,
            'show_quantity' => 15,
            'inventory_stock' => 15,
            'type' => 'variable',
            'condition' => 'Brand New',
        ]);

        $redVariant = $product->variants()->create([
            'color_name' => 'Red',
            'variant_name' => 'Mechanical Keyboard RGB - Red',
            'attributes' => ['color' => 'Red'],
            'condition' => 'Brand New',
            'inventory_stock' => 10,
            'show_quantity' => 10,
            'stock' => 10,
            'price' => 5000.00,
            'sale_price' => 4500.00,
        ]);

        $response = $this->actingAs($user)->postJson(route('checkout.process-order'), [
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+94771234567',
            'shipping_address_line_1' => '123 Main Street',
            'shipping_city' => 'Colombo',
            'subtotal' => 4500.00,
            'total_amount' => 4500.00,
            'items' => [
                [
                    'productId' => $product->id,
                    'variantId' => $redVariant->id,
                    'color' => 'Red',
                    'name' => 'Mechanical Keyboard RGB - Red',
                    'price' => 4500.00,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Refresh models from database
        $redVariant->refresh();
        $product->refresh();

        // Red variant stock should have dropped from 10 to 8
        $this->assertEquals(8, $redVariant->stock);
        $this->assertEquals(8, $redVariant->show_quantity);

        // Parent product quantity should have dropped from 15 to 13
        $this->assertEquals(13, $product->quantity);

        // OrderItem should link to variant_id and color
        $orderItem = OrderItem::where('product_id', $product->id)->latest('id')->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals($redVariant->id, $orderItem->variant_id);
        $this->assertEquals('Red', $orderItem->color);
        $this->assertEquals(2, $orderItem->quantity);
    }
}
