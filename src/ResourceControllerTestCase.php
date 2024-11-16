<?php

namespace FabTec\TestCraft;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Classe base para testes de controllers resource no Laravel.
 *
 * Essa classe encapsula os testes comuns para controllers que seguem o padrão resource.
 * Inclui métodos para testar as rotas `index`, `create`, `store`, `show`, `edit`, `update` e `destroy`.
 *
 * Para utilizá-la, estenda esta classe e configure as propriedades `$modelClass` e `$routePrefix`.
 */
abstract class ResourceControllerTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Classe do modelo que será testado.
     *
     * @var string
     */
    protected string $modelClass;

    /**
     * Prefixo das rotas relacionadas ao controller resource.
     *
     * Exemplo: para rotas como `users.index`, o prefixo seria `users`.
     *
     * @var string
     */
    protected string $routePrefix;

    /**
     * Define se o usuário será autenticado automaticamente.
     *
     * @var bool
     */
    protected bool $actingAs = true;
    /**
     * Configurações iniciais para os testes.
     *
     * Este método configura o banco de dados e autentica um usuário, se necessário.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticateUser();
    }

    /**
     * Autentica automaticamente um usuário, se habilitado.
     */
    private function authenticateUser(): void
    {
        if (!$this->actingAs) {
            return;
        }

        if (!class_exists(User::class) || !class_exists(UserFactory::class)) {
            $this->markTestSkipped('A classe User ou UserFactory não está disponível.');
            return;
        }

        $this->actingAs(User::factory()->create());
    }

    /**
     * Testa a rota `index` para verificar se a página é acessível.
     */
    public function testIndex(): void
    {
        $response = $this->get(route("{$this->routePrefix}.index"));
        $response->assertStatus(200);
    }

    /**
     * Testa a rota `create` para verificar se a página de criação é acessível.
     */
    public function testCreate(): void
    {
        $response = $this->get(route("{$this->routePrefix}.create"));
        $response->assertStatus(200);
    }

    /**
     * Testa a rota `store` para criar um novo registro.
     *
     * - Verifica o redirecionamento após o armazenamento.
     * - Confirma que os dados foram salvos no banco de dados.
     */
    public function testStore(): void
    {
        $createData = $this->generateCreateData();
        $response = $this->post(route("{$this->routePrefix}.store"), $createData);
        $response->assertRedirect(route("{$this->routePrefix}.index"));
        $this->assertDatabaseHas($this->modelClass, $createData);
    }

    /**
     * Testa a rota `show` para visualizar um registro específico.
     */
    public function testShow(): void
    {
        $modelInstance = $this->modelClass::factory()->create();
        $response = $this->get(route("{$this->routePrefix}.show", $modelInstance));
        $response->assertStatus(200);
    }

    /**
     * Testa a rota `edit` para verificar se a página de edição é acessível.
     */
    public function testEdit(): void
    {
        $modelInstance = $this->modelClass::factory()->create();
        $response = $this->get(route("{$this->routePrefix}.edit", $modelInstance));
        $response->assertStatus(200);
    }

    /**
     * Testa a rota `update` para atualizar um registro.
     *
     * - Executa o teste tanto para requisições PUT quanto PATCH.
     * - Verifica o redirecionamento após a atualização.
     * - Confirma que os dados foram atualizados no banco de dados.
     */
    public function testUpdate(): void
    {
        $modelInstance = $this->modelClass::factory()->create();

        $updateData = $this->generateUpdateData();

        // Teste com PUT
        $this->performUpdateTest('put', $modelInstance, $updateData);

        // Teste com PATCH
        $this->performUpdateTest('patch', $modelInstance, $updateData);
    }

    /**
     * Executa o teste para atualização de um registro usando PUT ou PATCH.
     *
     * @param string $method Método HTTP (`put` ou `patch`).
     * @param mixed $modelInstance Instância do modelo que será atualizada.
     * @param array<string, mixed> $updateData Dados para atualização.
     */
    private function performUpdateTest(string $method, $modelInstance, array $updateData): void
    {
        $response = $this->$method(route("{$this->routePrefix}.update", $modelInstance), $updateData);
        $response->assertRedirect(route("{$this->routePrefix}.index"));
        $this->assertDatabaseHas($this->modelClass, $updateData);
    }

    /**
     * Testa a rota `destroy` para excluir um registro.
     *
     * - Verifica o redirecionamento após a exclusão.
     * - Confirma que os dados foram removidos do banco de dados.
     */
    public function testDestroy(): void
    {
        $modelInstance = $this->modelClass::factory()->create();
        $response = $this->delete(route("{$this->routePrefix}.destroy", $modelInstance));
        $response->assertRedirect(route("{$this->routePrefix}.index"));
        $this->assertDatabaseMissing($this->modelClass, $modelInstance->toArray());
    }

    /**
     * Gera os dados para criação de um novo registro.
     *
     * Por padrão, delega a geração para o método `generateData`.
     *
     * @return array<string, mixed> Dados para criação.
     */
    protected function generateCreateData(): array
    {
        return $this->generateData();
    }

    /**
     * Gera os dados para atualização de um registro.
     *
     * Por padrão, delega a geração para o método `generateData`.
     *
     * @return array<string, mixed> Dados para atualização.
     */
    protected function generateUpdateData(): array
    {
        return $this->generateData();
    }

    /**
     * Método genérico para geração de dados.
     *
     * Deve ser sobrescrito na classe filha para fornecer os dados necessários.
     *
     * @return array<string, mixed> Dados genéricos.
     */
    protected function generateData(): array
    {
        return [];
    }
}
