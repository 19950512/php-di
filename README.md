# Container de Injeção de Dependência em PHP

## Visão Geral:

Este é um simples e flexível container de injeção de dependência em PHP, projetado para facilitar a gestão de dependências em seus projetos. Com este container, você pode vincular abstrações a implementações, resolver instâncias de objetos e gerenciar instâncias únicas com facilidade e segurança.

## Recursos:

- Vinculação de abstrações a implementações.
- Resolução de instâncias de objetos com base na abstração.
- Suporte a instâncias únicas.
- Funcionalidade de logging para monitoramento e depuração.
- Limpeza e remoção de instâncias do container.
- Implementação simples da interface PSR-11 (ContainerInterface).
- Facilidade de uso e integração com diferentes tipos de projetos PHP.

## Instalação:

Você pode instalar este container via Composer. Execute o seguinte comando no terminal:

```bash
composer require maydana/php-di
```

## Uso básico
```php
use SeuNamespace\Container;

// Criar uma instância do container
$container = new Container();

// Vincular uma abstração a uma implementação
$container->bind('SomeInterface', 'SomeImplementation');

// Resolver uma instância de objeto com base na abstração
$instance = $container->make('SomeInterface');

// Verificar se uma abstração está vinculada ou uma instância está resolvida
if ($container->has('SomeInterface')) {
    // Faça algo
}
```

## Contribuição:

Se você quiser contribuir com melhorias, correções de bugs ou novos recursos para este container, fique à vontade para abrir uma issue ou enviar um pull request no repositório do GitHub.

## Licença:

Este container de injeção de dependência é distribuído sob a licença MIT. Consulte o arquivo LICENSE para obter mais informações.