<?php
namespace JubisQA;
use PHPUnit\Framework\Attributes\Group;
#[Group('baseline')]
class EnvironmentTest extends IsolatedTestCase
{
    public function test_real_migrations_and_test_database_are_isolated(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertSame('array', config('mail.default'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('stock_movements'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('r_m_a_s'));
    }
}
