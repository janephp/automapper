<?php

namespace Jane\AutoMapper\Loader;

use Jane\AutoMapper\Generator\Generator;
use Jane\AutoMapper\MapperGeneratorMetadataInterface;
use PhpParser\PrettyPrinter\Standard;

/**
 * Use file system to load mapper, and persist them using a registry.
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
final class FileLoader implements ClassLoaderInterface
{
    private $generator;

    private $directory;

    private $hotReload;

    private $printer;

    private $registry;

    public function __construct(Generator $generator, string $directory, bool $hotReload = true)
    {
        $this->generator = $generator;
        $this->directory = $directory;
        $this->hotReload = $hotReload;
        $this->printer = new Standard();
    }

    /**
     * {@inheritdoc}
     */
    public function loadClass(MapperGeneratorMetadataInterface $mapperGeneratorMetadata): void
    {
        $className = $mapperGeneratorMetadata->getMapperClassName();
        $classPath = $this->directory . \DIRECTORY_SEPARATOR . $className . '.php';

        if (!$this->hotReload && file_exists($classPath)) {
            require $classPath;

            return;
        }

        $hash = $mapperGeneratorMetadata->getHash();
        $registry = $this->getRegistry();

        if (!isset($registry[$className]) || $registry[$className] !== $hash || !file_exists($classPath)) {
            $this->saveMapper($mapperGeneratorMetadata);
        }

        require $classPath;
    }

    public function saveMapper(MapperGeneratorMetadataInterface $mapperGeneratorMetadata): void
    {
        $className = $mapperGeneratorMetadata->getMapperClassName();
        $classPath = $this->directory . \DIRECTORY_SEPARATOR . $className . '.php';
        $hash = $mapperGeneratorMetadata->getHash();
        $classCode = $this->printer->prettyPrint([$this->generator->generate($mapperGeneratorMetadata)]);

        file_put_contents($classPath, "<?php\n\n" . $classCode . "\n");

        $this->addHashToRegistry($className, $hash);
    }

    private function addHashToRegistry($className, $hash): void
    {
        $registryPath = $this->directory . \DIRECTORY_SEPARATOR . 'registry.php';
        $this->registry[$className] = $hash;
        file_put_contents($registryPath, "<?php\n\nreturn " . var_export($this->registry, true) . ";\n");
    }

    private function getRegistry()
    {
        if (!file_exists($this->directory)) {
            mkdir($this->directory);
        }

        if (!$this->registry) {
            $registryPath = $this->directory . \DIRECTORY_SEPARATOR . 'registry.php';

            if (!file_exists($registryPath)) {
                $this->registry = [];
            } else {
                $this->registry = require $registryPath;
            }
        }

        return $this->registry;
    }
}
