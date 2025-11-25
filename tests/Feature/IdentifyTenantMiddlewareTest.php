<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentifyTenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function afterRefreshingDatabase()
    {
        // Ensure the central database is migrated (in-memory sqlite for testing)
        $this->artisan('migrate', [
            '--path' => 'database/migrations/central',
            '--database' => 'sqlite',
        ]);
    }

    public function test_it_identifies_tenant_by_host()
    {
        // Criar tenant no DB central
        $restaurant = Restaurant::create([
            'name' => 'Pizza Place',
            'slug' => 'pizza-place',
            'domain' => 'pizza.local',
            'db_name' => 'tenant_db',
            'active' => true,
            'contact_phone' => '123456789',
        ]);

        // Simular requisição com Host header correto
        // A rota /api (root do grupo tenant) deve responder
        $response = $this->getJson('http://pizza.local/api');

        if ($response->status() !== 200) {
            $this->fail(
                'Expected status 200 but received ' . $response->status() . '. Content: ' . $response->getContent()
            );
        }

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tenant connected',
                'tenant' => [
                    'id' => $restaurant->id,
                    'name' => 'Pizza Place',
                ],
            ]);
    }

    public function test_it_identifies_tenant_by_subdomain_with_delivery_suffix()
    {
        $restaurant = Restaurant::create([
            'name' => 'Burger Joint',
            'slug' => 'burger',
            'domain' => 'burger.com',
            'db_name' => 'tenant_2',
            'active' => true,
            'contact_phone' => '987654321',
        ]);

        // Simular subdomain com sufixo -delivery (ex: burger-delivery.meudominio.dev.br)
        $response = $this->getJson('http://burger-delivery.meudominio.dev.br/api');

        if ($response->status() !== 200) {
            $this->fail(
                'Expected status 200 but received ' . $response->status() . '. Content: ' . $response->getContent()
            );
        }

        $response->assertStatus(200)
            ->assertJsonPath('tenant.id', $restaurant->id);
    }

    public function test_it_identifies_tenant_by_simple_subdomain()
    {
        $restaurant = Restaurant::create([
            'name' => 'Sushi Place',
            'slug' => 'sushi',
            'domain' => 'sushi.com',
            'db_name' => 'tenant_3',
            'active' => true,
            'contact_phone' => '111222333',
        ]);

        // Simular subdomain simples (fallback para domínios customizados)
        $response = $this->getJson('http://sushi.meudominio.dev.br/api');

        if ($response->status() !== 200) {
            $this->fail(
                'Expected status 200 but received ' . $response->status() . '. Content: ' . $response->getContent()
            );
        }

        $response->assertStatus(200)
            ->assertJsonPath('tenant.id', $restaurant->id);
    }

    public function test_it_returns_404_when_tenant_not_found()
    {
        $response = $this->getJson('http://unknown.local/api');

        // Middleware retorna JSON 404
        $response->assertStatus(404)
            ->assertJson(['message' => 'Tenant not found or inactive']);
    }
}
