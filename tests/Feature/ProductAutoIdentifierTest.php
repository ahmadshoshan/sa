<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProductAutoIdentifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_code_and_ean13_barcode_are_generated_when_saving(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('master-data.manage', 'web'));
        $this->actingAs($user);

        $category = Category::create([
            'code' => 'AUTO-CATEGORY',
            'name' => 'Automatic identifiers',
            'is_active' => true,
        ]);
        $unit = Unit::create([
            'code' => 'AUTO-UNIT',
            'name' => 'Piece',
            'is_active' => true,
        ]);

        $this->get(route('products.create'))
            ->assertOk()
            ->assertSee('P000001')
            ->assertSee('2000000000015');

        $this->post(route('products.store'), [
            'name' => 'Generated product',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'cost_price' => 10,
            'sale_price' => 15,
        ])->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Generated product')->firstOrFail();

        $this->assertSame('P000001', $product->code);
        $this->assertSame('2000000000015', $product->barcode);
        $eanSum = 0;
        for ($index = 0; $index < 12; $index++) {
            $eanSum += (int) $product->barcode[$index] * ($index % 2 === 0 ? 1 : 3);
        }
        $this->assertSame(0, ($eanSum + (int) $product->barcode[12]) % 10);

        $this->post(route('products.store'), [
            'name' => 'Second generated product',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'cost_price' => 10,
            'sale_price' => 15,
        ])->assertRedirect(route('products.index'));

        $secondProduct = Product::where('name', 'Second generated product')->firstOrFail();
        $this->assertNotSame($product->code, $secondProduct->code);
        $this->assertNotSame($product->barcode, $secondProduct->barcode);
    }
}
