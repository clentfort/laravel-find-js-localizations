<?php

namespace clentfort\LaravelFindJsLocalizations\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use clentfort\LaravelFindJsLocalizations\Exceptions\RuntimeException;
use clentfort\LaravelFindJsLocalizations\KeySet;
use clentfort\LaravelFindJsLocalizations\PathHelper;

class KeySetTest extends TestCase
{
    protected $mockFs;
    protected $mockDir;
    protected $keySet;

    public function setUp()
    {
        $this->mockDir = 'some/dir';
        $this->mockFs = $this->createMock(Filesystem::class);
    }

    /**
     * KeySet is created because the specified path is an existing dir.
     */
    public function testKeySetConstructor()
    {
        $this->mockFs->expects($this->once())
            ->method('exists')
            ->with($this->mockDir)
            ->willReturn(true);

        $this->mockFs->expects($this->once())
            ->method('isDirectory')
            ->with($this->mockDir)
            ->willReturn(true);

        new KeySet($this->mockFs, $this->mockDir);
    }

    public function testKeySetConstructorPathDoesNotExist()
    {
        $this->mockFs->method('exists')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Directory \"{$this->mockDir}\" does not exists."
        );

        new KeySet($this->mockFs, $this->mockDir);
    }

    public function testKeySetConstructorPathIsNotDir()
    {
        $this->mockFs->method('exists')
            ->willReturn(true);
        $this->mockFs->method('isDirectory')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "\"{$this->mockDir}\" is not a directory."
        );

        new KeySet($this->mockFs, $this->mockDir);
    }

    private function setUpMockFs()
    {
        $this->mockFs->method('exists')
            ->willReturn(true);

        $this->mockFs->method('isDirectory')
            ->willReturn(true);
    }

    private function setUpKeySet()
    {
        $this->keySet = new KeySet($this->mockFs, $this->mockDir);
    }

    public function testGetKeysWithPrefixExistingFileThatIsArray()
    {
        $this->setUpMockFs();
        $this->setUpKeySet();

        $prefix = 'a';
        $fileContent = [
            'a' => 'some key',
            'b' => [
                'a' => 'some other key',
                'b' => 'last key',
            ],
        ];

        $filePath = PathHelper::join($this->mockDir, "${prefix}.php");

        $this->mockFs->expects($this->once())
            ->method('isFile')
            ->with($filePath)
            ->willReturn(true);

        $this->mockFs->expects($this->once())
            ->method('getRequire')
            ->with($filePath)
            ->willReturn($fileContent);

        $collection = $this->keySet->getKeysWithPrefix($prefix);

        $this->assertTrue($collection instanceof Collection);
        $this->assertEquals($collection->toArray(), Arr::dot($fileContent));
    }

    public function testGetKeysWithPrefixExistingFileThatIsNotArray()
    {
        $this->setUpMockFs();
        $this->setUpKeySet();

        $prefix = 'a';
        $fileContent = null;
        $filePath = PathHelper::join($this->mockDir, "${prefix}.php");

        $this->mockFs->expects($this->once())
            ->method('isFile')
            ->with($filePath)
            ->willReturn(true);

        $this->mockFs->expects($this->once())
            ->method('getRequire')
            ->with($filePath)
            ->willReturn($fileContent);

        $collection = $this->keySet->getKeysWithPrefix($prefix);

        $this->assertTrue($collection instanceof Collection);
        $this->assertEquals($collection->toArray(), []);
    }

    public function testGetKeysWithPrefixNotExisitingFile()
    {
        $this->setUpMockFs();
        $this->setUpKeySet();

        $prefix = 'a';
        $filePath = PathHelper::join($this->mockDir, "${prefix}.php");

        $this->mockFs->expects($this->once())
            ->method('isFile')
            ->with($filePath)
            ->willReturn(false);

        $collection = $this->keySet->getKeysWithPrefix($prefix);

        $this->assertTrue($collection instanceof Collection);
        $this->assertEquals($collection->toArray(), []);
    }

    public function testSetKeysWithPrefix()
    {
        $this->setUpMockFs();
        $this->setUpKeySet();

        $prefix = 'a';
        $filePath = PathHelper::join($this->mockDir, "${prefix}.php");
        $fileContent = [
            'a' => 'some key',
            'b' => [
                'a' => 'some other key',
                'b' => 'last key',
            ],
        ];

        $this->mockFs->expects($this->once())
            ->method('put')
            ->with(
                $filePath,
                $this->stringContains(
                    var_export(Arr::dot($fileContent), true))
                );

        $this->keySet->setKeysWithPrefix($prefix, $fileContent);
    }

}
