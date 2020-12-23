<?php

namespace Jane\AutoMapper\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Jane\AutoMapper\AutoMapper;
use Jane\AutoMapper\Generator\Generator;
use Jane\AutoMapper\Loader\ClassLoaderInterface;
use Jane\AutoMapper\Loader\FileLoader;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

/**
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 */
abstract class AutoMapperBaseTest extends TestCase
{
    /** @var AutoMapper */
    protected $autoMapper;

    /** @var ClassLoaderInterface */
    protected $loader;

    protected function setUp(): void
    {
        @unlink(__DIR__ . '/cache/registry.php');
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $this->loader = new FileLoader(new Generator(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            new ClassDiscriminatorFromClassMetadata($classMetadataFactory)
        ), __DIR__ . '/cache');

        $this->autoMapper = AutoMapper::create(true, $this->loader);
    }
}
