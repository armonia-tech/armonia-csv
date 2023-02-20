<?php
declare(strict_types=1);

namespace ArmoniaCsv\tests;

use PHPUnit\Framework\TestCase;
use ArmoniaCsv\Csv;

class CsvTest extends TestCase
{
    public function testHalfFullWidthSymbolAndSpaceContent()
    {
        $expected_output = [
            'id,仕入　先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '100,Warthman,pi_import_t、est_3,"testねemptyspa，ce","　　",1,84,958.11,ﾟ,"　　"',
            '"｡","｢","｣","､","･","ｦ","ｧ","ｨ","ｩ","ｪ","ｫ","ｬ","ｭ","ｮ",ｯ',
            'ﾀ,ﾁ,ﾂ,ﾃ,ﾄ,ﾅ,ﾆ,ﾇ,ﾈ,ﾉ,ﾊ,ﾋ,ﾌ,ﾍ,ﾎ,ﾏ',
            '"ﾐ","ﾑ","ﾒ","ﾓ","ﾔ","ﾕ","ﾖ","ﾗ","ﾘ","ﾙ","ﾚ","ﾛ","ﾜ","ﾝ",ﾞ,ﾟ',
            ' ア・イ・ウ・エ・オ'
        ];

        $content = $expected_output[0] . "
$expected_output[1]
$expected_output[2]
$expected_output[3]
$expected_output[4]
$expected_output[5]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['id', '仕入　先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['100', 'Warthman', 'pi_import_t、est_3', 'testねemptyspa，ce', '　　', '1', '84', '958.11', 'ﾟ', "　　"],
            ['｡','｢','｣','､','･','ｦ','ｧ','ｨ','ｩ','ｪ','ｫ','ｬ','ｭ','ｮ','ｯ'],
            ['ﾀ','ﾁ','ﾂ','ﾃ','ﾄ','ﾅ','ﾆ','ﾇ','ﾈ','ﾉ','ﾊ','ﾋ','ﾌ','ﾍ','ﾎ','ﾏ'],
            ['ﾐ','ﾑ','ﾒ','ﾓ','ﾔ','ﾕ','ﾖ','ﾗ','ﾘ','ﾙ','ﾚ','ﾛ','ﾜ','ﾝ','ﾞ','ﾟ'],
            [' ア・イ・ウ・エ・オ'],
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testSingleRowContent()
    {
        $content = 'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ';
        $expected_output = [
            $content
        ];
        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testSlashContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '100,Warth/man,pi_impo\'///rt_test_3,"/testsym""bol",19/,"1//""","""//84",958.11,15,"""1"'
        ];

        $content = $expected_output[0] . "
$expected_output[1]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['100', 'Warth/man', 'pi_impo\'///rt_test_3', '/testsym"bol', '19/', '1//"', '"//84', '958.11', '15', '"1'],
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testDoubleLinesContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
        ];

        $content = $expected_output[0] . "
$expected_output[1]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testMultiLinesContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '300,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
        ];

        $content = $expected_output[0] . "
$expected_output[1]
$expected_output[2]
$expected_output[3]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['300', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testEmptyLineExistsInLastRowContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '',
        ];

        $content = $expected_output[0] . "
$expected_output[1]
$expected_output[2]
$expected_output[3]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testEmptyLineExistsInMiddleRowContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
        ];

        $content = $expected_output[0] . "
$expected_output[1]
$expected_output[2]
$expected_output[3]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testEmptyLineExistsInFirstRowContent()
    {
        $expected_output = [
            '',
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
        ];

        $content = $expected_output[0] . "
$expected_output[1]
$expected_output[2]
$expected_output[3]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testEmptySpaceExistsInFirstColumnContent()
    {
        $expected_output = [
            ',仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '20  0,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '100 ,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            ',Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            ',Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
        ];

        $content = "$expected_output[0]
$expected_output[1]
$expected_output[2]
$expected_output[3]
$expected_output[4]";
       
        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['','仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['20  0', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['100 ', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testEmptySpaceExistsInMiddleColumnContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先   コード,原価通貨,原価2,原価,個数,タイプ',
            '200,Warthman,pi_import_test_3,"tests   empty",199,1,84,958.11,15,1',
            '200,Warthman,pi_import_test_3," testemptyspace",199,1,84,958.11,15,1',
            '200,Warthman,pi_import_test_3,"testemptyspace ",199,1,84,958.11,15,1',
            '100,Warthman,pi_import_test_3,"",199,1,84,958.11,15,1',
        ];

        $content = "$expected_output[0]
$expected_output[1]
$expected_output[2]
$expected_output[3]
$expected_output[4]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['id','仕入先', 'PI No', 'SKU/パーツ', '仕入先   コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['200', 'Warthman', 'pi_import_test_3', 'tests   empty', '199', '1', '84', '958.11', '15', '1'],
            ['200', 'Warthman', 'pi_import_test_3', ' testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace ', '199', '1', '84', '958.11', '15', '1'],
            ['100', 'Warthman', 'pi_import_test_3', '', '199', '1', '84', '958.11', '15', '1']
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testEmptySpaceExistsInLastColumnContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイ プ',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1 ',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1 ',
        ];

        $content = "$expected_output[0]
$expected_output[1]
$expected_output[2]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
        
        $expected_output = [
            ['id','仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイ プ'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1 '],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1 ']
        ];
        $this->assertEquals($expected_output, $result);
    }

    public function testDoubleQuotesExistsInLastColumnContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,"タイ""プ"',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"1"""',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"""1"',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"""""1"',
        ];

        $content = "$expected_output[0]
$expected_output[1]
$expected_output[2]
$expected_output[3]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['id','仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイ"プ'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1"'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '"1'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '""1']
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testDoubleQuotesExistsInFirstColumnContent()
    {
        $expected_output = [
            '"""id",仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,"タイプ"',
            '"""200",Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"1"',
            '"100""",Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"1"',
            '"""""100",Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"1"',
            '"""""yy""",Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"1"',
        ];

        $content = "$expected_output[0]
$expected_output[1]
$expected_output[2]
$expected_output[3]
$expected_output[4]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['"id','仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['"200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['100"', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['""100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['""yy"', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1']
        ];
        
        $this->assertEquals($expected_output, $result);
    }

    public function testDoubleQuotesExistsInMiddleColumnContent()
    {
        $expected_output = [
            '仕入先,"""id",PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,"タイプ"',
            'Warthman,"""200",pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"1"',
            'Warthman,"100""",pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"1"',
            'Warthman,"""""100",pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"1"',
            'Warthman,"""""yy""",pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"1"',
        ];

        $content = "$expected_output[0]
$expected_output[1]
$expected_output[2]
$expected_output[3]
$expected_output[4]";

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['仕入先', '"id', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['Warthman', '"200', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', '100"', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', '""100', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', '""yy"', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1']
        ];
        
        $this->assertEquals($expected_output, $result);
    }

    public function testNewlineExistsInFirstColumnContent()
    {
        $expected_output = [
            '"仕入\n先コード",仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ"',
            '"\ntestemptyspace",Warthman,"200",pi_import_test_3,199,1,84,958.11,15,"1"',
            '"testemptyspace\n",Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1"',
            '"\ntestemptyspace\n",Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1"',
            '"testempt\nyspace",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"',
            '"testempt\n\n            yspace",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"',
            '"\ntestempt\n\n            yspace\n",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"',
        ];

        $content = '"仕入
先コード",仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ"
"
testemptyspace",Warthman,"200",pi_import_test_3,199,1,84,958.11,15,"1"
"testemptyspace
",Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1"
"
testemptyspace
",Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1"
"testempt
yspace",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"
"testempt

            yspace",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"
"
testempt

            yspace
",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"';

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['仕入
先コード','仕入先', 'id', 'PI No', 'SKU/パーツ',  '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            [ '
testemptyspace', 'Warthman', '200', 'pi_import_test_3','199', '1', '84', '958.11', '15', '1'],
            ['testemptyspace
', 'Warthman', '100', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1'],
            ['
testemptyspace
','Warthman', '100', 'pi_import_test_3',  '199', '1', '84', '958.11', '15', '1'],
            ['testempt
yspace', 'Warthman', 'yy', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1'],
            ['testempt

            yspace','Warthman', 'yy', 'pi_import_test_3',  '199', '1', '84', '958.11', '15', '1'],
            ['
testempt

            yspace
','Warthman', 'yy', 'pi_import_test_3',  '199', '1', '84', '958.11', '15', '1']
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testNewlineExistsInMiddleColumnContent()
    {
        $expected_output = [
            '仕入先,"id",PI No,SKU/パーツ,"仕入\n先コード",原価通貨,原価2,原価,個数,"タイプ"',
            'Warthman,"200",pi_import_test_3,"\ntestemptyspace",199,1,84,958.11,15,"1"',
            'Warthman,"100",pi_import_test_3,"testemptyspace\n",199,1,84,958.11,15,"1"',
            'Warthman,"100",pi_import_test_3,"\ntestemptyspace\n",199,1,84,958.11,15,"1"',
            'Warthman,"yy",pi_import_test_3,"testempt\nyspace",199,1,84,958.11,15,"1"',
            'Warthman,"yy",pi_import_test_3,"testempt\n\n            yspace",199,1,84,958.11,15,"1"',
            'Warthman,"yy",pi_import_test_3,"\ntestempt\n\n            yspace\n",199,1,84,958.11,15,"1"',
        ];

        $content = '仕入先,"id",PI No,SKU/パーツ,"仕入
先コード",原価通貨,原価2,原価,個数,"タイプ"
Warthman,"200",pi_import_test_3,"
testemptyspace",199,1,84,958.11,15,"1"
Warthman,"100",pi_import_test_3,"testemptyspace
",199,1,84,958.11,15,"1"
Warthman,"100",pi_import_test_3,"
testemptyspace
",199,1,84,958.11,15,"1"
Warthman,"yy",pi_import_test_3,"testempt
yspace",199,1,84,958.11,15,"1"
Warthman,"yy",pi_import_test_3,"testempt

            yspace",199,1,84,958.11,15,"1"
Warthman,"yy",pi_import_test_3,"
testempt

            yspace
",199,1,84,958.11,15,"1"';

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['仕入先', 'id', 'PI No', 'SKU/パーツ', '仕入
先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['Warthman', '200', 'pi_import_test_3', '
testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', '100', 'pi_import_test_3', 'testemptyspace
', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', '100', 'pi_import_test_3', '
testemptyspace
', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', 'yy', 'pi_import_test_3', 'testempt
yspace', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', 'yy', 'pi_import_test_3', 'testempt

            yspace', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', 'yy', 'pi_import_test_3', '
testempt

            yspace
', '199', '1', '84', '958.11', '15', '1']
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testNewlineExistsInLastColumnContent()
    {
        $expected_output = [
            '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入\n先コード"',
            'Warthman,"200",pi_import_test_3,199,1,84,958.11,15,"1","\ntestemptyspace"',
            'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace\n"',
            'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","\ntestemptyspace\n"',
            'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt\nyspace"',
            'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt\n\n            yspace"',
            'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","\ntestempt\n\n            yspace\n"',
        ];

        $content = '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入
先コード"
Warthman,"200",pi_import_test_3,199,1,84,958.11,15,"1","
testemptyspace"
Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace
"
Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","
testemptyspace
"
Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt
yspace"
Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt

            yspace"
Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","
testempt

            yspace
"';

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals($expected_output, $parsed_result);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['仕入先', 'id', 'PI No', 'SKU/パーツ', '原価通貨', '原価2', '原価', '個数', 'タイプ', '仕入
先コード'],
            ['Warthman', '200', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', '
testemptyspace'],
            ['Warthman', '100', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', 'testemptyspace
'],
            ['Warthman', '100', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', '
testemptyspace
'],
            ['Warthman', 'yy', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', 'testempt
yspace'],
            ['Warthman', 'yy', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', 'testempt

            yspace'],
            ['Warthman', 'yy', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', '
testempt

            yspace
']
        ];

        $this->assertEquals($expected_output, $result);
    }

    public function testNewlineExistsContent()
    {
        $expected_output = [
            '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入\n先コード"',
            '"\nWarthman","200",pi_import_test_3,199,1,84,958.11,15,"1","\ntestemptyspace"',//here
            'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace\n"',
            '"   Warthman\n","100",pi_import_test_3,199,1,84,958.11,15,"1","\ntestemptyspace\n"',
            '"\n    Warthman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt\nyspace"',//here
            '"War\n\nthman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt\n\n            yspace"',
            'Warthman,"yy",pi_import_test_3,199,1,"8\n    4",958.11,15,"1","\ntestempt\n\n            yspace\n"',
        ];

        $content = '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入
先コード"
"
Warthman","200",pi_import_test_3,199,1,84,958.11,15,"1","
testemptyspace"
Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace
"
"   Warthman
","100",pi_import_test_3,199,1,84,958.11,15,"1","
testemptyspace
"
"
    Warthman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt
yspace"
"War

thman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt

            yspace"
Warthman,"yy",pi_import_test_3,199,1,"8
    4",958.11,15,"1","
testempt

            yspace
"';

        $parsed_result = Csv::parseCsvContentToArray($content);

        $this->assertEquals(count($expected_output), count($parsed_result));
        $this->assertEquals($expected_output[0], $parsed_result[0]);
        $this->assertEquals($expected_output[1], $parsed_result[1]);
        $this->assertEquals($expected_output[2], $parsed_result[2]);
        $this->assertEquals($expected_output[3], $parsed_result[3]);
        $this->assertEquals($expected_output[4], $parsed_result[4]);
        $this->assertEquals($expected_output[5], $parsed_result[5]);
        $this->assertEquals($expected_output[6], $parsed_result[6]);

        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $expected_output = [
            ['仕入先', 'id', 'PI No', 'SKU/パーツ', '原価通貨', '原価2', '原価', '個数', 'タイプ', '仕入
先コード'],
            ['
Warthman', '200', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', '
testemptyspace'],
            ['Warthman', '100', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', 'testemptyspace
'],
            ['   Warthman
', '100', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', '
testemptyspace
'],
            ['
    Warthman', 'yy', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', 'testempt
yspace'],
            ['War

thman', 'yy', 'pi_import_test_3', '199', '1', '84', '958.11', '15', '1', 'testempt

            yspace'],
            ['Warthman', 'yy', 'pi_import_test_3', '199', '1', '8
    4', '958.11', '15', '1', '
testempt

            yspace
']
        ];

        $this->assertEquals(count($expected_output), count($result));
        $this->assertEquals($expected_output[0], $result[0]);
        $this->assertEquals($expected_output[1], $result[1]);
        $this->assertEquals($expected_output[2], $result[2]);
        $this->assertEquals($expected_output[3], $result[3]);
        $this->assertEquals($expected_output[4], $result[4]);
        $this->assertEquals($expected_output[5], $result[5]);
        $this->assertEquals($expected_output[6], $result[6]);
    }
}
