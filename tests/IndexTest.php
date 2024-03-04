<?php

namespace App;

use Exception;
use App\Container;
use Psr\Log\LoggerInterface;

class SimpleLogger implements LoggerInterface
{
     // propriedade para armazenar o último log registrado
     private ?string $lastLog = null;

     // implementação de outros métodos da interface LoggerInterface...
 
     // Método para obter o último log registrado
     public function getLastLog(): ?string
     {
         return $this->lastLog;
     }
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $datetime = date('Y-m-d H:i:s');
        $formattedMessage = "[$datetime] [$level]: $message";
        $this->lastLog = $formattedMessage;
        echo $formattedMessage;
    }
}

// Classe de teste para SomeClass
class SomeClass
{
    public function __construct()
    {
        //
    }
}

// Classe de teste para AnotherClass
class AnotherClass
{
    public SomeClass $someClass;

    public function __construct(SomeClass $someClass)
    {
        $this->someClass = $someClass;
    }
}

// Testes
test('Instância de Container', function () {
    $container = new Container();
    expect($container)->toBeInstanceOf(Container::class);
});

test('Container é compativel com ContainerInterface PSR 11', function(){
    $container = new Container();
    expect($container)->toBeInstanceOf(\Psr\Container\ContainerInterface::class);

});

test('Bind e Make', function () {
    $container = new Container();
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    $instance = $container->make(SomeClass::class);
    expect($instance)->toBeInstanceOf(SomeClass::class);
});

test('Bind e Make com dependência', function () {
    $container = new Container();
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    $container->bind(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });
    $instance = $container->make(AnotherClass::class);
    expect($instance)->toBeInstanceOf(AnotherClass::class);
    expect($instance->someClass)->toBeInstanceOf(SomeClass::class);
});

test('Bind e Make com dependência resolvida', function () {
    $container = new Container();
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    $container->bind(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });
    $instance = $container->make(AnotherClass::class);
    expect($instance)->toBeInstanceOf(AnotherClass::class);
    expect($instance->someClass)->toBeInstanceOf(SomeClass::class);
});

test('Bind e Make com dependência resolvida e instância única', function () {
    $container = new Container();

    // Registrando SomeClass como instância única
    $container->singleton(SomeClass::class, function ($container) {
        return new SomeClass();
    });

    // Registrando AnotherClass como instância única, com SomeClass como dependência
    $container->bind(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });

    // Obtendo duas instâncias de AnotherClass
    $instance1 = $container->make(AnotherClass::class);
    $instance2 = $container->make(AnotherClass::class);

    // Verificando se as instâncias são as mesmas
    expect($instance1)->toBe($instance2);

    // Verificando se as dependências de SomeClass nas duas instâncias são as mesmas
    expect($instance1->someClass)->toBe($instance2->someClass);
});

test('Has', function () {
    $container = new Container();
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    expect($container->has(SomeClass::class))->toBeTrue();
    expect($container->has(AnotherClass::class))->toBeFalse();
});

test('Get', function () {
    $container = new Container();
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    $instance = $container->get(SomeClass::class);
    expect($instance)->toBeInstanceOf(SomeClass::class);
});

test('Get com dependência', function () {
    $container = new Container();
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    $container->bind(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });
    $instance = $container->get(AnotherClass::class);
    expect($instance)->toBeInstanceOf(AnotherClass::class);
    expect($instance->someClass)->toBeInstanceOf(SomeClass::class);
});

test('Get com dependência resolvida', function () {
    $container = new Container();
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    $container->bind(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });
    $instance = $container->get(AnotherClass::class);
    expect($instance)->toBeInstanceOf(AnotherClass::class);
    expect($instance->someClass)->toBeInstanceOf(SomeClass::class);
});

test('Get com dependência resolvida e instância única', function () {
    $container = new Container();

    // Registrando SomeClass como instância única
    $container->singleton(SomeClass::class, function ($container) {
        return new SomeClass();
    });

    // Registrando AnotherClass como instância única, com SomeClass como dependência
    $container->bind(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });

    // Obtendo duas instâncias de AnotherClass
    $instance1 = $container->get(AnotherClass::class);
    $instance2 = $container->get(AnotherClass::class);

    // Verificando se as instâncias são as mesmas
    expect($instance1)->toBe($instance2);

    // Verificando se as dependências de SomeClass nas duas instâncias são as mesmas
    expect($instance1->someClass)->toBe($instance2->someClass);
});

test('Singleton', function () {
    $container = new Container();
    $container->singleton(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    $instance1 = $container->make(SomeClass::class);
    $instance2 = $container->make(SomeClass::class);
    expect($instance1)->toBe($instance2);
});

test('Singleton com dependência', function () {
    $container = new Container();
    $container->singleton(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    $container->singleton(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });
    $instance1 = $container->make(AnotherClass::class);
    $instance2 = $container->make(AnotherClass::class);
    expect($instance1)->toBe($instance2);
    expect($instance1->someClass)->toBe($instance2->someClass);
});

test('Singleton com dependência resolvida', function () {
    $container = new Container();
    $container->singleton(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    $container->singleton(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });
    $instance1 = $container->make(AnotherClass::class);
    $instance2 = $container->make(AnotherClass::class);
    expect($instance1)->toBe($instance2);
    expect($instance1->someClass)->toBe($instance2->someClass);
});

test('Singleton com dependência resolvida e instância única', function () {
    $container = new Container();

    // Registrando SomeClass como instância única
    $container->singleton(SomeClass::class, function ($container) {
        return new SomeClass();
    });

    // Registrando AnotherClass como instância única, com SomeClass como dependência
    $container->singleton(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });

    // Obtendo duas instâncias de AnotherClass
    $instance1 = $container->make(AnotherClass::class);
    $instance2 = $container->make(AnotherClass::class);

    // Verificando se as instâncias são as mesmas
    expect($instance1)->toBe($instance2);

    // Verificando se as dependências de SomeClass nas duas instâncias são as mesmas
    expect($instance1->someClass)->toBe($instance2->someClass);
});

test('Make com classe inexistente', function () {
    $container = new Container();
    // Tentar obter uma instância de uma classe inexistente deve lançar uma exceção
    $container->make('ClasseInexistente');
})->throws(Exception::class, 'Class "ClasseInexistente" does not exist');

test('Make com dependência inexistente', function () {
    $container = new Container();
    $container->bind('ClasseDependente', function ($container) {
        return new AnotherClass($container->make('ClasseInexistente'));
    });
    // Tentar obter uma instância de uma classe com dependência inexistente deve lançar uma exceção
    $container->make('ClasseDependente');
})->throws(Exception::class, 'Class "ClasseInexistente" does not exist');

test('Remoção de instâncias', function () {
    $container = new Container();
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });

    // Verifique se o contêiner tem a instância registrada
    expect($container->has(SomeClass::class))->toBeTrue();

    // Remova a instância do contêiner
    $container->remove(SomeClass::class);

    // Verifique se a instância foi removida corretamente
    expect($container->has(SomeClass::class))->toBeFalse();
});

test('Limpeza de registros', function () {
    $container = new Container();
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });
    $container->bind(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });

    // Limpe todos os registros do contêiner
    $container->clear();

    // Verifique se o contêiner está vazio após a limpeza
    expect($container->has(SomeClass::class))->toBeFalse();
    expect($container->has(AnotherClass::class))->toBeFalse();
});

test('Sobrescrita de instâncias', function () {
    $container = new Container();
    
    // Instância original de SomeClass
    $originalInstance = new SomeClass();
    
    // Registrar a instância original no contêiner
    $container->bind(SomeClass::class, function ($container) use ($originalInstance) {
        return $originalInstance;
    });

    // Verificar se o contêiner tem a instância original registrada
    expect($container->has(SomeClass::class))->toBeTrue();

    // Sobrescrever a instância com uma nova implementação
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });

    // Verificar se a instância original foi substituída pela nova implementação
    expect($container->has(SomeClass::class))->toBeTrue();

    // Obter a instância após a sobrescrita
    $newInstance = $container->make(SomeClass::class);

    // Verificar se a nova instância é diferente da original
    expect($newInstance)->not->toBe($originalInstance);
});

test('Alias de classes', function () {
    $container = new Container();

    // Registrar uma classe original no contêiner
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });

    // Criar um alias para a classe original
    $container->alias('AliasClasse', SomeClass::class);

    // Verificar se o alias foi criado corretamente
    expect($container->has('AliasClasse'))->toBeTrue();

    // Verificar se o alias resolve para a mesma instância de SomeClass
    $instance = $container->make('AliasClasse');
    expect($instance)->toBeInstanceOf(SomeClass::class);

    // Verificar se o alias resolve para a mesma instância de SomeClass
    $instance = $container->get(SomeClass::class);
    expect($instance)->toBeInstanceOf(SomeClass::class);
});

test('Resolução de dependência', function () {
    $container = new Container();

    // Registrar a classe SomeClass no contêiner
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });

    // Registrar a classe AnotherClass no contêiner, que depende de SomeClass
    $container->bind(AnotherClass::class, function ($container) {
        return new AnotherClass($container->make(SomeClass::class));
    });

    // Verificar se o contêiner é capaz de resolver as dependências corretamente
    $instance = $container->make(AnotherClass::class);
    expect($instance)->toBeInstanceOf(AnotherClass::class);
    expect($instance->someClass)->toBeInstanceOf(SomeClass::class);
});

test('Teste de performance', function () {
    $container = new Container();
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });

    $iterations = 10000; // Número de iterações para criar instâncias
    $start = microtime(true); // Captura o tempo inicial

    // Executa a criação de instâncias várias vezes
    for ($i = 0; $i < $iterations; $i++) {
        $instance = $container->make(SomeClass::class);
    }

    $end = microtime(true); // Captura o tempo final
    $executionTime = $end - $start; // Calcula o tempo de execução em segundos

    // Verifica se o tempo de execução está dentro de um limite aceitável
    $maxExecutionTime = 1; // Limite máximo de tempo de execução em segundos
    expect($executionTime)->toBeLessThanOrEqual($maxExecutionTime);
});

test('Teste de Carga Máxima', function () {
    $container = new Container();

    // Registrar um grande número de instâncias
    for ($i = 0; $i < 10000; $i++) {
        $container->bind("Instance{$i}", function ($container) {
            return new SomeClass();
        });
    }

    // Resolver uma grande quantidade de dependências
    for ($i = 0; $i < 10000; $i++) {
        $instance = $container->make("Instance{$i}");
        expect($instance)->toBeInstanceOf(SomeClass::class);
    }
});

test('Teste de Solicitações Concorrentes', function () {
    $container = new Container();

    // Número de solicitações concorrentes
    $concurrentRequests = 10;

    // Arrays para armazenar os resultados e erros de cada solicitação
    $results = [];
    $errors = [];

    // Função para resolver uma instância de SomeClass
    $resolveInstance = function () use ($container) {
        return $container->make(SomeClass::class);
    };

    // Simulação de várias solicitações concorrentes
    $promises = [];
    for ($i = 1; $i <= $concurrentRequests; $i++) {
        // Crie uma promessa assíncrona para cada solicitação
        $promises[] = \React\Promise\resolve()->then($resolveInstance);
    }

    // Espere que todas as promessas sejam resolvidas
    \React\Promise\all($promises)->then(
        function ($resolvedInstances) use (&$results) {
            // Armazene os resultados de todas as solicitações
            $results = $resolvedInstances;
        },
        function ($error) use (&$errors) {
            // Armazene o erro, se ocorrer algum
            $errors[] = $error;
        }
    );

    // Verifique se não houve erros
    expect($errors)->toBeEmpty();

    // Verifique se todas as instâncias foram resolvidas corretamente
    expect(count($results))->toBe($concurrentRequests);
});



test('Teste de Vazamento de Memória', function () {
    $container = new Container();

    // Registrar um grande número de instâncias
    for ($i = 0; $i < 10000; $i++) {
        $container->bind("Instance{$i}", function ($container) {
            return new SomeClass();
        });
    }

    // Limpar o contêiner
    $container->clear();
    // Verificar se o contêiner está vazio após a limpeza
    $isEmpty = true;
    for ($i = 0; $i < 10000; $i++) {
        if ($container->has("Instance{$i}")) {
            $isEmpty = false;
            break;
        }
    }

    expect($isEmpty)->toBeTrue();
});

test('Teste de Escopo - Singleton', function () {
    $container = new Container();

    $container->singleton(SomeClass::class, function ($container) {
        return new SomeClass();
    });

    // Obter a instância pela primeira vez
    $instance1 = $container->make(SomeClass::class);

    // Obter a instância novamente
    $instance2 = $container->make(SomeClass::class);

    // Verificar se as instâncias são as mesmas
    expect($instance1)->toBe($instance2);
});

test('Teste de Logging e Monitoramento', function () {
    $container = new Container();

    // Configurar um logger simples para o contêiner
    $container->setLogger(new SimpleLogger());

    // Registrar uma classe no contêiner
    $container->bind(SomeClass::class, function ($container) {
        return new SomeClass();
    });

    // Verificar se o logger registrou a operação de binding corretamente
    expect($container->getLogger()->getLastLog())->toContain("Binding 'App\SomeClass' to 'Closure'");

    // Resolva a classe do contêiner
    $instance = $container->make(SomeClass::class);

    // Verificar se o logger registrou a operação de resolução corretamente
    expect($container->getLogger()->getLastLog())->toContain("Resolving 'App\SomeClass' using 'Closure'");
});

