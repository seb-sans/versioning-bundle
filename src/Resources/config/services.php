<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shivas\VersioningBundle\Command\ListProvidersCommand;
use Shivas\VersioningBundle\Command\StatusCommand;
use Shivas\VersioningBundle\Command\VersionBumpCommand;
use Shivas\VersioningBundle\Formatter\FormatterInterface;
use Shivas\VersioningBundle\Formatter\GitDescribeFormatter;
use Shivas\VersioningBundle\Provider\GitRepositoryProvider;
use Shivas\VersioningBundle\Provider\InitialVersionProvider;
use Shivas\VersioningBundle\Provider\RevisionProvider;
use Shivas\VersioningBundle\Provider\VersionProvider;
use Shivas\VersioningBundle\Service\VersionManager;
use Shivas\VersioningBundle\Service\VersionManagerInterface;
use Shivas\VersioningBundle\Twig\VersionExtension;
use Shivas\VersioningBundle\Writer\VersionWriter;
use Shivas\VersioningBundle\Writer\WriterInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->public(false);

    $services->alias(VersionManagerInterface::class, 'shivas_versioning.manager');
    $services->alias(FormatterInterface::class, 'shivas_versioning.formatter.git');
    $services->alias(WriterInterface::class, 'shivas_versioning.writer.version');

    $services->set('shivas_versioning.manager', VersionManager::class)
        ->args([
            service('shivas_versioning.cache.version'),
            service(WriterInterface::class),
            service(FormatterInterface::class),
        ]);

    $services->set('shivas_versioning.formatter.git', GitDescribeFormatter::class);

    $services->set('shivas_versioning.writer.version', VersionWriter::class)
        ->args(['%kernel.project_dir%']);

    $services->set('shivas_versioning.provider.version', VersionProvider::class)
        ->args(['%kernel.project_dir%'])
        ->tag('shivas_versioning.provider', ['alias' => 'version', 'priority' => 100]);

    $services->set('shivas_versioning.provider.git', GitRepositoryProvider::class)
        ->args(['%kernel.project_dir%'])
        ->tag('shivas_versioning.provider', ['alias' => 'git', 'priority' => -25]);

    $services->set('shivas_versioning.provider.revision', RevisionProvider::class)
        ->args(['%kernel.project_dir%'])
        ->tag('shivas_versioning.provider', ['alias' => 'revision', 'priority' => -50]);

    $services->set('shivas_versioning.provider.init', InitialVersionProvider::class)
        ->tag('shivas_versioning.provider', ['alias' => 'init', 'priority' => -75]);

    $services->set('shivas_versioning.cache.version')
        ->parent('cache.system')
        ->tag('cache.pool');

    $services->set('shivas_versioning.command.status', StatusCommand::class)
        ->args([service(VersionManagerInterface::class)])
        ->tag('console.command');

    $services->set('shivas_versioning.command.list_providers', ListProvidersCommand::class)
        ->args([service(VersionManagerInterface::class)])
        ->tag('console.command');

    $services->set('shivas_versioning.command.version_bump', VersionBumpCommand::class)
        ->args([service(VersionManagerInterface::class)])
        ->tag('console.command');

    $services->set('shivas_versioning.twig.version', VersionExtension::class)
        ->args([service(VersionManagerInterface::class)])
        ->tag('twig.extension');
};
