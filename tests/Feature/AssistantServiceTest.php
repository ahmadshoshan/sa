<?php

namespace Tests\Feature;

use App\Services\AssistantService;
use App\Models\Product;
use App\Models\Invoice;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssistantServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $assistant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assistant = app(AssistantService::class);
    }

    /** @test */
    public function it_understands_greeting()
    {
        $result = $this->assistant->answer('مرحبا');
        $this->assertStringContainsString('أهلاً', $result['answer']);
    }

    /** @test */
    public function it_understands_egyptian_dialect()
    {
        $result = $this->assistant->answer('ازيك عامل ايه');
        $this->assertStringContainsString('أهلاً', $result['answer']);
    }

    /** @test */
    public function it_calculates_today_sales()
    {
        // إنشاء فاتورة اليوم
        Invoice::factory()->create([
            'type' => 'sale',
            'invoice_date' => today(),
            'total' => 1000,
        ]);

        $result = $this->assistant->answer('ما مبيعات اليوم؟');
        $this->assertStringContainsString('1,000', $result['answer']);
    }

    /** @test */
    public function it_finds_product_details()
    {
        Product::factory()->create(['name' => 'حديد 5 لينيه', 'sale_price' => 500]);

        $result = $this->assistant->answer('ما تفاصيل حديد 5 لينيه؟');
        $this->assertStringContainsString('حديد 5 لينيه', $result['answer']);
    }

    /** @test */
    public function it_handles_modify_command()
    {
        $product = Product::factory()->create([
            'name' => 'اسمنت الصفوه',
            'sale_price' => 5000,
        ]);

        $result = $this->assistant->answer('زود سعر اسمنت الصفوه 100');
        $this->assertStringContainsString('5,100', $result['answer']);
        
        $product->refresh();
        $this->assertEquals(5100, $product->sale_price);
    }

    /** @test */
    public function it_learns_from_questions()
    {
        // أول مرة
        $result1 = $this->assistant->answer('ما عدد العملاء؟');
        
        // ثاني مرة (يجب أن يكون أسرع من الذاكرة)
        $result2 = $this->assistant->answer('ما عدد العملاء؟');
        
        $this->assertDatabaseHas('assistant_memory', [
            'question' => 'ما عدد العملاء؟',
        ]);
    }

    /** @test */
    public function it_navigates_to_pages()
    {
        $result = $this->assistant->answer('افتح نقطة البيع');
        $this->assertStringContainsString('/pos', $result['answer']);
        $this->assertArrayHasKey('link', $result);
    }

    /** @test */
    public function it_provides_smart_suggestions()
    {
        $result = $this->assistant->answer('اقترح عليّ');
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertNotEmpty($result['suggestions']);
    }
}