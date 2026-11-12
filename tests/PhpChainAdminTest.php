<?php

declare(strict_types=1);

use Oliverde8\Component\PhpEtl\ChainConfig;
use Oliverde8\PhpEtlBundle\Etl\ChainDefinitionInterface\ChainDefinitionInterface;
use Oliverde8\PhpEtlBundle\Services\ChainProcessorsManager;
use Oliverde8\PhpEtlSyliusAdminBundle\Etl\ChainDefinitionFormatter;
use Oliverde8\PhpEtlSyliusAdminBundle\Form\Type\Etl\EtlExecutionType;
use Oliverde8\PhpEtlSyliusAdminBundle\Twig\Components\NewEtlComponent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;

final class PhpChainAdminTest extends TestCase
{
    public function testFormIncludesPhpAndYamlDefinitions(): void
    {
        $manager = $this->createMock(ChainProcessorsManager::class);
        $manager->method('getRawDefinitions')->willReturn(['legacy' => 'chain: []']);
        $definition = $this->createMock(ChainDefinitionInterface::class);
        $definition->method('getKey')->willReturn('php-demo');

        $factory = Forms::createFormFactoryBuilder()
            ->addType(new EtlExecutionType($manager, [$definition]))
            ->getFormFactory();
        $choices = $factory->create(EtlExecutionType::class)->createView()->children['name']->vars['choices'];

        self::assertSame(['legacy', 'php-demo'], array_map(static fn ($choice) => $choice->value, $choices));
    }

    public function testComponentDisplaysPhpAndLegacyDefinitions(): void
    {
        $definition = new class() implements ChainDefinitionInterface {
            public function getKey(): string
            {
                return 'php-demo';
            }

            public function build(): ChainConfig
            {
                return new ChainConfig();
            }
        };
        $manager = $this->createMock(ChainProcessorsManager::class);
        $manager->method('getRawDefinition')->willReturnMap([
            ['php-demo', $definition],
            ['legacy', 'chain: []'],
        ]);
        $component = new NewEtlComponent($manager);

        self::assertSame('', $component->getRawDefinition());
        $component->formValues = ['name' => 'php-demo'];
        self::assertSame(ChainDefinitionFormatter::PHP_PREFIX . $definition::class, $component->getRawDefinition());
        $component->formValues = ['name' => 'legacy'];
        self::assertSame('chain: []', $component->getRawDefinition());
    }
}
