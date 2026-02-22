<?php

namespace Tests\Unit;

class TemplateTest extends \PHPUnit\Framework\TestCase
{
    public function testSetSwitcherWorksWithDefaultAttribute(): void
    {
        $template = new \HemiFrame\Template('<wSwitcher id="test" default="10"><case value="10">Ten</case><case value="20">Twenty</case></wSwitcher>');

        $template->setSwitcher('test', '20');

        $this->assertSame('Twenty', $template->parse());
    }

    public function testParseUsesDefaultSwitcherValueWhenNotSet(): void
    {
        $template = new \HemiFrame\Template('<wSwitcher id="test" default="10"><case value="10">Ten</case><case value="20">Twenty</case></wSwitcher>');

        $this->assertSame('Ten', $template->parse());
    }

    public function testParseThrowsWhenDefaultCaseDoesNotExist(): void
    {
        $template = new \HemiFrame\Template('<wSwitcher id="test" default="30"><case value="10">Ten</case><case value="20">Twenty</case></wSwitcher>');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Can't find case with value '30' in switcher with ID 'test'");

        $template->parse();
    }

    public function testParseClearsUnresolvedSwitcherWithAutoRemoveEnabled(): void
    {
        $template = new \HemiFrame\Template('A<wSwitcher id="test"><case value="10">Ten</case></wSwitcher>B');

        $this->assertSame('AB', $template->parse());
    }

    public function testParseKeepsUnresolvedSwitcherWhenAutoRemoveDisabled(): void
    {
        $template = new \HemiFrame\Template('A<wSwitcher id="test"><case value="10">Ten</case></wSwitcher>B');

        $template->autoRemoveSwitchers(false);

        $this->assertSame('A<wSwitcher id="test"><case value="10">Ten</case></wSwitcher>B', $template->parse());
    }

    public function testSetSwitcherThrowsWhenIdDoesNotExist(): void
    {
        $template = new \HemiFrame\Template('<wSwitcher id="test" default="10"><case value="10">Ten</case></wSwitcher>');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Can't find switcher with ID:missing");

        $template->setSwitcher('missing', '10');
    }

    public function testParseUsesDefaultWhenAttributeOrderDiffers(): void
    {
        $template = new \HemiFrame\Template('<wSwitcher default="10" id="test"><case value="10">Ten</case><case value="20">Twenty</case></wSwitcher>');

        $this->assertSame('Ten', $template->parse());
    }

    public function testSetSwitcherReplacesAllSwitcherOccurrencesById(): void
    {
        $template = new \HemiFrame\Template('<wSwitcher id="test" default="10"><case value="10">A</case><case value="20">B</case></wSwitcher>-<wSwitcher id="test" default="10"><case value="10">C</case><case value="20">D</case></wSwitcher>');

        $template->setSwitcher('test', '20');

        $this->assertSame('B-D', $template->parse());
    }
}
