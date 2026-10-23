<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_filters_by_keyword_and_genre(): void
    {
        $this->seed();
        $response = $this->get('/search?q=Cửu+Châu');
        $response->assertStatus(200);
        $response->assertSee('Cửu Châu Thần Ma Lục');

        $emptyResponse = $this->get('/search?q=NonExistentKeywordXYZ');
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('Không tìm thấy tác phẩm');
    }
}
