# TestCraft

## Introdução

O **TestCraft** é um pacote para Laravel que facilita a criação de classes de teste para controllers do tipo resource. Ele inclui um comando de terminal para geração automática de testes e uma classe base que abstrai as operações comuns de testes em controllers resource.

Este pacote foi desenvolvido com o objetivo de:
- **Padronizar** a escrita de testes em projetos Laravel.
- **Agilizar** o processo de desenvolvimento, reduzindo a necessidade de criar testes manualmente.
- **Aumentar a eficiência** no ciclo de desenvolvimento, garantindo que testes essenciais sejam gerados automaticamente.

---

## Instalação

Para adicionar este pacote ao seu projeto Laravel, siga os passos abaixo:

### 1. Adicionar o Repositório ao `composer.json`

Como este pacote está hospedado em um repositório Git privado, você deve configurar o repositório no seu arquivo `composer.json`. Adicione a seguinte entrada:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:fabtec-ifc/TestCraft.git"
    }
  ]
}
```

### 2. Instalar o Pacote

Depois de configurar o repositório, instale o pacote executando:

```bash
composer require fabtec/test-craft
```

---

## Uso

### Comando `testcraft:create`

O comando principal do pacote é `testcraft:create`, que gera uma classe de teste para um controlador resource com base no nome da entidade.

#### Sintaxe:
```bash
php artisan testcraft:create {entity}
```

#### Parâmetros:
- **`{entity}`**: Nome da entidade relacionada ao controller (exemplo: `User`, `Product`).

#### O que o Comando Faz:
1. **Valida Requisitos**:
   - Verifica a existência do modelo: `App\Models\{Entity}`.
   - Verifica a existência da fábrica: `Database\Factories\{Entity}Factory`.
   - Verifica a existência do controller: `App\Http\Controllers\{Entity}Controller`.
   - Verifica se as rotas esperadas do padrão resource estão registradas.

2. **Gera a Classe de Teste**:
   - Cria a classe no diretório `tests/Feature/Http/Controllers`.
   - O nome do arquivo será `{Entity}ControllerTest.php`.

3. **Configura os Testes**:
   - Define a classe do modelo (`$modelClass`).
   - Define o prefixo de rota (`$routePrefix`).
   - Inclui métodos para geração de dados utilizados nos testes (`generateData()`).

---

## Classe Base: `ResourceControllerTestCase`

As classes geradas herdam de `ResourceControllerTestCase`, que abstrai as operações comuns de testes em controllers resource.

### Atributos Customizáveis

- **`$modelClass`**:
  - Define a classe do modelo relacionado ao controller.
  - Exemplo:
    ```php
    protected string $modelClass = \App\Models\Product::class;
    ```

- **`$routePrefix`**:
  - Define o prefixo das rotas relacionadas ao controller.
  - Exemplo:
    ```php
    protected string $routePrefix = 'products';
    ```

### Métodos Customizáveis

- **`generateData()`**:
  - Retorna os dados necessários para criar ou atualizar registros nos testes.
  - É utilizado nos testes das rotas `store` e `update`.
  - Exemplo:
    ```php
    protected function generateData(): array
    {
        return [
            'name' => 'Product Test',
            'price' => 100.0,
        ];
    }
    ```

- **`generateCreateData()`** e **`generateUpdateData()`**:
  - Herdados de `ResourceControllerTestCase`.
  - Delegam por padrão para `generateData()` mas podem ser sobrescritos para fornecer dados diferentes.
  - Exemplo:
    ```php
    protected function generateCreateData(): array
    {
        return [
            'name' => 'New Product',
            'price' => 150.0,
        ];
    }
    ```

---

## Exemplo de Classe Gerada

Abaixo está um exemplo de uma classe de teste gerada para o `ProductController`:

```php
namespace Tests\Feature\Http\Controllers;

use App\Models\Product;
use FabTec\TestCraft\ResourceControllerTestCase;

class ProductControllerTest extends ResourceControllerTestCase
{
    /**
     * {@inheritDoc}
     */
    protected string $modelClass = Product::class;

    /**
     * {@inheritDoc}
     */
    protected string $routePrefix = 'products';

    /**
     * {@inheritDoc}
     */
    protected function generateData(): array
    {
        return [
            'name' => 'Product Test',
            'price' => 100.0,
        ];
    }
}
```

---

## O que esta Classe Faz?

- Testa automaticamente as rotas padrão de controllers resource:
  - **`index`**: Lista todos os registros.
  - **`create`**: Exibe o formulário de criação.
  - **`store`**: Cria um novo registro.
  - **`show`**: Exibe um registro específico.
  - **`edit`**: Exibe o formulário de edição.
  - **`update`**: Atualiza um registro.
  - **`destroy`**: Exclui um registro.

---

## Contribuição

Para contribuir com o **TestCraft**, abra um pull request ou reporte problemas no repositório oficial do GitHub.

---

## Autores

- Henrique Borges dos Santos [https://github.com/HenriqueBS0](https://github.com/HenriqueBS0)  

---

## Licença

Este pacote é licenciado sob a [MIT License](LICENSE).

---
