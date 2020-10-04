<?php

declare(strict_types=1);

namespace Tests;

use Controllers\ControllerMain;
use PHPUnit\Framework\TestCase;

class ControllerMainTest extends TestCase
{
    public object $main;

    protected function setUp(): void
    {
        $this->main = new ControllerMain();
    }

    public function testEmpty()
    {
        $stack = [];
        $this->assertEmpty($stack);

        return $stack;
    }

    /**
     * @depends      testEmpty
     * @dataProvider additionProviderCreateArray
     * @param $stack
     */
    public function testCreateArray($stack)
    {
        $this->assertNotEmpty($stack);
    }

    public function additionProviderCreateArray()
    {
        $main = new ControllerMain();

        return [
            [$main->exampleCsv()],
            [$main->exampleXml()]
        ];
    }

    /**
     * @dataProvider additionProviderTrim
     * @param $actual
     */
    public function testTrim($actual)
    {
        $this->assertSame('TestName', $this->main->trimSpecialCharacters($actual));
    }

    public function additionProviderTrim()
    {
        return [
            ['&acute;TestName'],
            ['TestNam&uml;e'],
            ['T&circ;estName'],
            ['Te&grave;stName'],
            ['TestN&ring;ame'],
            ['TestNam&lig;e'],
            ['TestN&quot;ame'],
            ['TestName&rsquo;'],
            ['Tes’tName']
        ];
    }
}
