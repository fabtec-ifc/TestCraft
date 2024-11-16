<?php

namespace FabTec\TestCraft\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;
use Illuminate\Filesystem\Filesystem;

class CreateTestCommand extends Command
{
    protected $signature = "testcraft:create {entity : Nome da entidade (e.g., TipoUsuario)}";
    protected $description = "Cria uma classe de teste estendendo ResourceControllerTestCase";

    public function handle(): int
    {
        $entity = $this->argument('entity');
        $errors = $this->validateRequirements($entity);

        // Exibe todos os erros encontrados
        if (!empty($errors)) {
            $this->displayErrors($errors);
            return self::FAILURE;
        }

        // Preparar dados para a criação do arquivo
        $modelClassNamespace = "App\\Models\\{$entity}";
        $routePrefix = Str::camel($entity);
        $filePath = base_path("tests/Feature/Http/Controllers/{$entity}ControllerTest.php");

        // Verifica se o arquivo já existe
        if ($this->fileExistsAndUserDisagrees($filePath)) {
            $this->info('Operação cancelada.');
            return self::SUCCESS;
        }

        // Gera o conteúdo do stub
        $stubPath = __DIR__ . '/../../stubs/resource-controller-test.stub';
        $stubContent = $this->generateStubContent($stubPath, [
            '{{namespace}}' => 'Tests\\Feature\\Http\\Controllers',
            '{{className}}' => "{$entity}ControllerTest",
            '{{modelClassNamespace}}' => $modelClassNamespace,
            '{{modelClass}}' => $entity,
            '{{routePrefix}}' => $routePrefix,
        ]);

        // Salva o arquivo de teste
        $this->saveFile($filePath, $stubContent);

        $this->info("Classe de teste <fg=yellow;options=underscore>{$entity}ControllerTest</> criada com sucesso");

        return self::SUCCESS;
    }

    /**
     * Valida os requisitos necessários para criar o teste.
     */
    private function validateRequirements(string $entity): array
    {
        $errors = [];
        $modelClassNamespace = "App\\Models\\{$entity}";
        $factoryClass = "Database\\Factories\\{$entity}Factory";
        $controllerClass = "App\\Http\\Controllers\\{$entity}Controller";
        $routePrefix = Str::camel($entity);

        // Valida a existência do modelo
        if (!class_exists($modelClassNamespace)) {
            $errors[] = "O modelo {$modelClassNamespace} não foi encontrado.";
        }

        // Valida a existência da fábrica
        if (!class_exists($factoryClass)) {
            $errors[] = "A fábrica {$factoryClass} para o modelo {$entity} não foi encontrada.";
        }

        // Valida a existência do controller
        if (!class_exists($controllerClass)) {
            $errors[] = "O controller {$controllerClass} não foi encontrado.";
        }

        // Valida os métodos do controller
        if (class_exists($controllerClass)) {
            $missingMethods = $this->findMissingControllerMethods($controllerClass);
            if (!empty($missingMethods)) {
                $errors[] = "O controller {$controllerClass} está faltando os seguintes métodos: " . implode(', ', $missingMethods);
            }
        }

        // Valida a existência das rotas
        $missingRoutes = $this->findMissingRoutes($routePrefix);
        if (!empty($missingRoutes)) {
            $errors[] = "As seguintes rotas para o prefixo {$routePrefix} não foram encontradas: " . implode(', ', $missingRoutes);
        }

        return $errors;
    }

    /**
     * Exibe todos os erros encontrados ao usuário.
     */
    private function displayErrors(array $errors): void
    {
        $this->error("Os seguintes problemas foram encontrados:");
        foreach ($errors as $error) {
            $this->error("- {$error}");
        }
    }

    /**
     * Verifica se o controller possui todos os métodos necessários e retorna os faltantes.
     */
    private function findMissingControllerMethods(string $controllerClass): array
    {
        $requiredMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
        $reflection = new ReflectionClass($controllerClass);
        $methods = array_map(fn($method) => $method->name, $reflection->getMethods());

        return array_diff($requiredMethods, $methods);
    }

    /**
     * Verifica se as rotas necessárias existem para o prefixo e retorna as faltantes.
     */
    private function findMissingRoutes(string $routePrefix): array
    {
        $requiredRoutes = [
            "{$routePrefix}.index",
            "{$routePrefix}.create",
            "{$routePrefix}.store",
            "{$routePrefix}.show",
            "{$routePrefix}.edit",
            "{$routePrefix}.update",
            "{$routePrefix}.destroy",
        ];

        $missingRoutes = [];
        foreach ($requiredRoutes as $route) {
            if (!Route::has($route)) {
                $missingRoutes[] = $route;
            }
        }

        return $missingRoutes;
    }

    /**
     * Verifica se o arquivo já existe e solicita confirmação ao usuário.
     */
    private function fileExistsAndUserDisagrees(string $filePath): bool
    {
        if (file_exists($filePath)) {
            return !$this->confirm("O arquivo já existe. Deseja sobrescrevê-lo?");
        }
        return false;
    }

    /**
     * Gera o conteúdo do stub substituindo os placeholders.
     */
    private function generateStubContent(string $stubPath, array $replacements): string
    {
        $stub = file_get_contents($stubPath);
        foreach ($replacements as $key => $value) {
            $stub = str_replace($key, $value, $stub);
        }
        return $stub;
    }

    /**
     * Salva o arquivo de teste no sistema de arquivos.
     */
    private function saveFile(string $filePath, string $content): void
    {
        $filesystem = new Filesystem();

        // Cria o diretório se não existir
        if (!$filesystem->exists(dirname($filePath))) {
            $filesystem->makeDirectory(dirname($filePath), 0755, true);
        }

        // Salva o conteúdo no arquivo
        $filesystem->put($filePath, $content);
    }
}
