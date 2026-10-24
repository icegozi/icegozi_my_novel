<?php

namespace Tests\Unit;

use App\Helpers\FormulaSanitizerHelper;
use PHPUnit\Framework\TestCase;

class FormulaSanitizerTest extends TestCase
{
    public function test_prefixes_formula_injection_characters_with_single_quote(): void
    {
        // Equal sign
        $this->assertEquals("'=1+1", FormulaSanitizerHelper::sanitize('=1+1'));
        $this->assertEquals("'=cmd|' /C calc'!A0", FormulaSanitizerHelper::sanitize('=cmd|\' /C calc\'!A0'));

        // Plus sign
        $this->assertEquals("'+1+1", FormulaSanitizerHelper::sanitize('+1+1'));

        // Minus sign
        $this->assertEquals("'-2+5", FormulaSanitizerHelper::sanitize('-2+5'));

        // At symbol
        $this->assertEquals("'@SUM(A1:A10)", FormulaSanitizerHelper::sanitize('@SUM(A1:A10)'));

        // Tab character
        $this->assertEquals("'\ttab_value", FormulaSanitizerHelper::sanitize("\ttab_value"));

        // Carriage return
        $this->assertEquals("'\rreturn_val", FormulaSanitizerHelper::sanitize("\rreturn_val"));
    }

    public function test_preserves_safe_text_without_modification(): void
    {
        $this->assertEquals('Tiểu thuyết kỳ ảo', FormulaSanitizerHelper::sanitize('Tiểu thuyết kỳ ảo'));
        $this->assertEquals('Chapter 10: The Beginning', FormulaSanitizerHelper::sanitize('Chapter 10: The Beginning'));
        $this->assertEquals('12345', FormulaSanitizerHelper::sanitize('12345'));
        $this->assertEquals('', FormulaSanitizerHelper::sanitize(''));
    }

    public function test_sanitizes_entire_csv_row_array(): void
    {
        $row = [
            'title' => '=HYPERLINK("http://evil.com")',
            'author' => 'Nguyễn Du',
            'views' => 1500,
            'summary' => '@CMD injection test',
        ];

        $sanitized = FormulaSanitizerHelper::sanitizeRow($row);

        $this->assertEquals("'=HYPERLINK(\"http://evil.com\")", $sanitized['title']);
        $this->assertEquals('Nguyễn Du', $sanitized['author']);
        $this->assertEquals('1500', $sanitized['views']);
        $this->assertEquals("'@CMD injection test", $sanitized['summary']);
    }
}
