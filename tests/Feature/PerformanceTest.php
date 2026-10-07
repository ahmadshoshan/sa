<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dashboard_loads_under_500ms()
    {
        // إنشاء بيانات واقعية
        $this->createTestData(100, 50);

        $start = microtime(true);
        $response = $this->get(route('dashboard.index'));
        $time = (microtime(true) - $start) * 1000; // ms

        $response->assertStatus(200);
        $this->assertLessThan(500, $time, "Dashboard took {$time}ms to load");
    }

    /** @test */
    public function products_list_loads_under_300ms()
    {
        $this->createTestData(1000, 100);

        $start = microtime(true);
        $response = $this->get(route('products.index'));
        $time = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $time, "Products list took {$time}ms to load");
    }

    /** @test */
    public function assistant_responds_under_200ms()
    {
        $start = microtime(true);
        $response = $this->postJson(route('assistant.ask'), [
            'question' => 'ما مبيعات اليوم؟',
        ]);
        $time = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(200, $time, "Assistant took {$time}ms to respond");
    }

    private function createTestData($productsCount, $invoicesCount)
    {
        $products = \App\Models\Product::factory()->count($productsCount)->create();
        
        for ($i = 0; $i < $invoicesCount; $i++) {
            $invoice = Invoice::factory()->create([
                'type' => 'sale',
                'invoice_date' => now()->subDays(rand(1, 30)),
            ]);

            for ($j = 0; $j < rand(1, 5); $j++) {
                InvoiceItem::factory()->create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $products->random()->id,
                ]);
            }
        }
    }
}