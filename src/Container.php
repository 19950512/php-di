<?php

declare(strict_types=1);

namespace App;

use Closure;
use Exception;
use ReflectionClass;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    // Array para armazenar instâncias de objetos resolvidas
    private array $instances = [];

    // Array para armazenar as ligações entre abstrações e implementações
    private array $bindings = [];

    // Logger para registrar mensagens de erro e depuração do contêiner de injeção de dependência
    private ?LoggerInterface $logger = null;

    // Método para vincular uma abstração a uma implementação
    public function bind(string $abstract, $concreto): void
    {
        $this->logDebug("Binding '{$abstract}' to '".$concreto::class."'");
        $this->bindings[$abstract] = $concreto;
    }

    // Método para resolver uma instância de objeto com base na abstração
    public function make(string $abstract)
    {
        // Se a instância já foi resolvida, retorna ela diretamente
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Verifica se a abstração tem uma implementação vinculada
        $concreto = $this->bindings[$abstract] ?? $abstract;

        // Se a implementação for uma função anônima, resolve a instância
        if ($concreto instanceof Closure) {
            $this->logDebug("Resolving '{$abstract}' using a Closure");
            return $this->resolverInstancia($abstract, $concreto);
        }

        // Cria um reflexo da classe concreta para resolver suas dependências
        $reflection = new ReflectionClass($concreto);
        $constructor = $reflection->getConstructor();

        // Se a classe não tiver construtor, resolve a instância
        if ($constructor === null) {
            $this->logDebug("Resolving '{$abstract}' with no constructor dependencies");
            return $this->resolverInstancia($abstract, $reflection);
        }

        // Obtém os parâmetros do construtor para resolver as dependências
        $parameters = $constructor->getParameters();
        $dependencias = [];

        // Resolve cada dependência do construtor recursivamente
        foreach ($parameters as $parameter) {
            $tipoDependencia = $parameter->getType();

            // Verifica se o tipo da dependência está definido
            if ($tipoDependencia === null) {
                $message = "Não é possível resolver o parâmetro {$parameter->name} para {$concreto}.";
                $this->logError($message);
                throw new Exception($message);
            }

            // Obtém o nome da classe da dependência
            $classeDependencia = $tipoDependencia->getName();

            // Resolve a dependência e adiciona à lista de dependências
            $this->logDebug("Resolving dependency '{$classeDependencia}' for '{$concreto}'");
            $dependencias[] = $this->make($classeDependencia);
        }

        // Cria uma nova instância da classe com as dependências resolvidas
        $instancia = $reflection->newInstanceArgs($dependencias);

        // Armazena a instância resolvida para uso futuro
        $this->instances[$abstract] = $instancia;
        $this->logDebug("Instance '{$abstract}' resolved successfully");

        // Retorna a instância resolvida
        return $instancia;
    }

    public function alias(string $alias, string $abstract): void
    {
        $this->logDebug("Creating alias '{$alias}' for '{$abstract}'");
        $this->bindings[$alias] = $abstract;
    }

    // Método para obter uma instância de objeto com base na abstração
    public function get($id)
    {
        $this->logDebug("Resolving '{$id}' using get() method");
        return $this->make($id);
    }

    // Método para verificar se uma abstração está vinculada ou uma instância está resolvida
    public function has($id): bool
    {
        $hasBinding = isset($this->bindings[$id]);
        $hasInstance = isset($this->instances[$id]);
        if ($hasBinding) {
            $this->logDebug("Container has binding for '{$id}'");
        } elseif ($hasInstance) {
            $this->logDebug("Container has instance for '{$id}'");
        } else {
            $this->logDebug("Container does not have '{$id}'");
        }
        return $hasBinding || $hasInstance;
    }

    public function remove(string $abstract): void
    {
        $this->logDebug("Removing '{$abstract}' from container");
        unset($this->instances[$abstract], $this->bindings[$abstract]);
    }

    public function clear(): void
    {
        $this->logDebug("Clearing container");
        $this->instances = [];
        $this->bindings = [];
    }

    // Método para vincular uma abstração a uma instância única usando um closure
    public function singleton(string $abstract, Closure $concreto): void
    {

        // Define um closure que cria uma instância única e a mantém em cache
        $this->logDebug("Binding '{$abstract}' as singleton");
        $this->bindings[$abstract] = function () use ($concreto) {
            static $instancia;
            if (!isset($instancia)) {
                $instancia = $concreto($this);
            }
            return $instancia;
        };
    }

    // Método para registrar mensagens de erro e depuração
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    // Método privado para resolver uma instância de objeto
    private function resolverInstancia(string $abstract, $concreto)
    {
        // Resolve a instância e a armazena para uso futuro
        $this->logDebug("Resolving '{$abstract}' using '".$concreto::class."'");
        return $this->instances[$abstract] = $concreto($this);
    }

    // Métodos para registrar mensagens de erro e depuração
    private function logDebug(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->debug($message);
        }
    }

    private function logError(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->error($message);
        }
    }
}
