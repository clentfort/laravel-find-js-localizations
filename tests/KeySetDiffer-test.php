<?php

namespace clentfort\LaravelFindJsLocalizations\Tests;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use clentfort\LaravelFindJsLocalizations\KeySet;
use clentfort\LaravelFindJsLocalizations\KeySetDiffer;

class KeySetDifferTest extends TestCase
{
    /**
     * DiffKeys with all keys in the key-set that are in the key-list
     */
    public function testDiffKeysWithoutMissingKeys()
    {
        $mockKeySet = $this->createMock(KeySet::class);
        $mockKeySet->expects($this->once())
            ->method('getKeysWithPrefix')
            ->with('some')
            ->willReturn(new Collection([
                'a' => 'Some Key',
                'b' => 'Some other key',
            ]));

        $keyList = new Collection([
            'some.a',
            'some.b',
        ]);

        $this->assertEquals(
            KeySetDiffer::diffKeys($mockKeySet, $keyList)->toArray(),
            []
        );
    }

    /**
     * DiffKeys with one key in the key-list that is not in the key-set.
     */
    public function testDiffKeysWithMissingKey()
    {
        $mockKeySet = $this->createMock(KeySet::class);
        $mockKeySet->expects($this->once())
            ->method('getKeysWithPrefix')
            ->with('some')
            ->willReturn(new Collection([
                'a' => 'Some Key',
                'b' => 'Some other key',
            ]));

        $keyList = new Collection([
            'some.a',
            'some.b',
            'some.c',
        ]);

        $diff = KeySetDiffer::diffKeys($mockKeySet, $keyList);

        $this->assertEquals(
            ['some'],
            $diff->keys()->toArray()
        );

        $someGroup = $diff['some'];
        $this->assertEquals(
            ['c'],
            array_values($someGroup->toArray()) // Ensure the array-indices
                                                // start at 0
        );
    }
}
