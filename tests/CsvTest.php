<?php
declare(strict_types=1);

namespace ArmoniaCsv\tests;

use PHPUnit\Framework\TestCase;
use ArmoniaCsv\Csv;
use ArmoniaGoogle\CloudStorage;
class CsvTest extends TestCase
{
    public function testEmptyContent()
    {
        $expected_output = [
            ''
        ];

        $content = '';

        $expected_output_to_array = [];
        $parsed_result = Csv::parseCsvContentToArray($content);
        $this->assertEquals($expected_output, $parsed_result);
        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
        $this->assertEquals($expected_output_to_array, $result);
    }

    public function testMultiNewlineWithSingleRowContent()
    {
        $expected_output = [
            '1,1,"22\n33",4,"55\n66","7",8'
        ];

        $contents = [];
        $contents[] = '1,1,"22'."\n".'33",4,"55'."\n".'66","7",8';
        $contents[] = '1,1,"22'."\r".'33",4,"55'."\r".'66","7",8';
        $contents[] = '1,1,"22'."\r\n".'33",4,"55'."\r\n".'66","7",8';

        $expected_output_to_array = [
            [ '1','1','22'."\n".'33', '4', '55'."\n".'66', '7', '8'],
        ];
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
    }

    public function testDoubleQuotesContent()
    {
        $expected_output = [
            'a,b,c',
            '"1""doublehere",2,3',
            ',,"\n"',
            '1,"1""doublehere",1'
        ];

        $contents = [];
        $contents[] = 'a,b,c'."\n".'"1""doublehere",2,3'."\n".',,"'."\n".'"'."\n".'1,"1""doublehere",1';
        $contents[] = 'a,b,c'."\r".'"1""doublehere",2,3'."\r".',,"'."\r".'"'."\r".'1,"1""doublehere",1';
        $contents[] = 'a,b,c'."\r\n".'"1""doublehere",2,3'."\r\n".',,"'."\r\n".'"'."\r\n".'1,"1""doublehere",1';

        $expected_output_to_array = [
            ['a', 'b', 'c'],
            ['1"doublehere', '2', '3'],
            ['', '', "\n"],
            ['1', '1"doublehere', '1'],
        ];
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);

            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
    }

    public function testHalfFullWidthSymbolAndSpaceContent()
    {
        $expected_output = [
            'id,仕入　先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,"\n",タイプ',
            '100,Warthman,pi_import_t、est_3,"testねemptyspa，ce","　　",1,84,958.11,ﾟ,"　　"',
            '"｡","｢","｣","､","･","ｦ","ｧ","ｨ","ｩ","ｪ","ｫ","ｬ","ｭ","ｮ",ｯ',
            'ﾀ,ﾁ,ﾂ,ﾃ,ﾄ,ﾅ,ﾆ,ﾇ,ﾈ,ﾉ,ﾊ,ﾋ,ﾌ,ﾍ,ﾎ,ﾏ',
            '"ﾐ","ﾑ","ﾒ","ﾓ","ﾔ","ﾕ","ﾖ","ﾗ","ﾘ","ﾙ","ﾚ","ﾛ","ﾜ","ﾝ",ﾞ,ﾟ',
            ' ア・イ・ウ・エ・オ'
        ];

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]\n$expected_output[2]\n$expected_output[3]\n$expected_output[4]\n$expected_output[5]"; 
        $contents[] = $expected_output[0] ."\r". $expected_output[1] ."\r". $expected_output[2] ."\r". $expected_output[3] ."\r". $expected_output[4] ."\r". $expected_output[5];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1] ."\r\n". $expected_output[2] ."\r\n". $expected_output[3] ."\r\n". $expected_output[4] ."\r\n". $expected_output[5];

        $expected_output_to_array = [
            ['id', '仕入　先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', "\n",'タイプ'],
            ['100', 'Warthman', 'pi_import_t、est_3', 'testねemptyspa，ce', '　　', '1', '84', '958.11', 'ﾟ', "　　"],
            ['｡','｢','｣','､','･','ｦ','ｧ','ｨ','ｩ','ｪ','ｫ','ｬ','ｭ','ｮ','ｯ'],
            ['ﾀ','ﾁ','ﾂ','ﾃ','ﾄ','ﾅ','ﾆ','ﾇ','ﾈ','ﾉ','ﾊ','ﾋ','ﾌ','ﾍ','ﾎ','ﾏ'],
            ['ﾐ','ﾑ','ﾒ','ﾓ','ﾔ','ﾕ','ﾖ','ﾗ','ﾘ','ﾙ','ﾚ','ﾛ','ﾜ','ﾝ','ﾞ','ﾟ'],
            [' ア・イ・ウ・エ・オ'],
        ];
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);

            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
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

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]";
        $contents[] = $expected_output[0] ."\r". $expected_output[1];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1];

        $expected_output_to_array = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['100', 'Warth/man', 'pi_impo\'///rt_test_3', '/testsym"bol', '19/', '1//"', '"//84', '958.11', '15', '"1'],
        ];

        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
    }

    public function testDoubleLinesContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
        ];

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]";
        $contents[] = $expected_output[0] ."\r". $expected_output[1];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1];

        $expected_output_to_array = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
    }

    public function testMultiLinesContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '300,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
        ];

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]\n$expected_output[2]\n$expected_output[3]"; 
        $contents[] = $expected_output[0] ."\r". $expected_output[1] ."\r". $expected_output[2] ."\r". $expected_output[3];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1] ."\r\n". $expected_output[2] ."\r\n". $expected_output[3];

        $expected_output_to_array = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['300', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];

        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
    }

    public function testEmptyLineExistsInLastRowContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '',
        ];

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]\n$expected_output[2]\n$expected_output[3]"; 
        $contents[] = $expected_output[0] ."\r". $expected_output[1] ."\r". $expected_output[2] ."\r". $expected_output[3];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1] ."\r\n". $expected_output[2] ."\r\n". $expected_output[3];

        $expected_output_to_array = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];
        
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
    }

    // special case handle by armonia csv
    // if empty line exists in middle, then must group all group together with previous line
    public function testEmptyLineExistsInMiddleRowContent()
    {
        $data = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
        ];

        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,"タイプ\n\n200",Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
        ];

        $contents = [];
        $contents[] = $data[0] . "\n$data[1]\n$data[2]\n$data[3]";
        $contents[] = $data[0] ."\r". $data[1] ."\r". $data[2] ."\r". $data[3];
        $contents[] = $data[0] ."\r\n". $data[1] ."\r\n". $data[2] ."\r\n". $data[3];

        $expected_output_to_array = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', "タイプ\n\n200", 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];

        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
    }

    public function testEmptyLineExistsInFirstRowContent()
    {
        $expected_output = [
            '',
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイプ',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1',
        ];

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]\n$expected_output[2]\n$expected_output[3]"; 
        $contents[] = $expected_output[0] ."\r". $expected_output[1] ."\r". $expected_output[2] ."\r". $expected_output[3];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1] ."\r\n". $expected_output[2] ."\r\n". $expected_output[3];

        $expected_output_to_array = [
            ['id', '仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];
        
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
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

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]\n$expected_output[2]\n$expected_output[3]\n$expected_output[4]"; 
        $contents[] = $expected_output[0] ."\r". $expected_output[1] ."\r". $expected_output[2] ."\r". $expected_output[3] ."\r". $expected_output[4];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1] ."\r\n". $expected_output[2] ."\r\n". $expected_output[3] ."\r\n". $expected_output[4];

        $expected_output_to_array = [
            ['','仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['20  0', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['100 ', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
        ];
        
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
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

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]\n$expected_output[2]\n$expected_output[3]\n$expected_output[4]"; 
        $contents[] = $expected_output[0] ."\r". $expected_output[1] ."\r". $expected_output[2] ."\r". $expected_output[3] ."\r". $expected_output[4];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1] ."\r\n". $expected_output[2] ."\r\n". $expected_output[3] ."\r\n". $expected_output[4];

        $expected_output_to_array = [
            ['id','仕入先', 'PI No', 'SKU/パーツ', '仕入先   コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['200', 'Warthman', 'pi_import_test_3', 'tests   empty', '199', '1', '84', '958.11', '15', '1'],
            ['200', 'Warthman', 'pi_import_test_3', ' testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace ', '199', '1', '84', '958.11', '15', '1'],
            ['100', 'Warthman', 'pi_import_test_3', '', '199', '1', '84', '958.11', '15', '1']
        ];
        
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
    }

    public function testEmptySpaceExistsInLastColumnContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,タイ プ',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1 ',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,1 ',
        ];

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]\n$expected_output[2]"; 
        $contents[] = $expected_output[0] ."\r". $expected_output[1] ."\r". $expected_output[2];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1] ."\r\n". $expected_output[2];

        $expected_output_to_array = [
            ['id','仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイ プ'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1 '],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1 ']
        ];
        
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
    }

    public function testDoubleQuotesExistsInLastColumnContent()
    {
        $expected_output = [
            'id,仕入先,PI No,SKU/パーツ,仕入先コード,原価通貨,原価2,原価,個数,"タイ""プ"',
            '200,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"1"""',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"""1"',
            '100,Warthman,pi_import_test_3,"testemptyspace",199,1,84,958.11,15,"""""1"',
        ];

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]\n$expected_output[2]\n$expected_output[3]"; 
        $contents[] = $expected_output[0] ."\r". $expected_output[1] ."\r". $expected_output[2] ."\r". $expected_output[3];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1] ."\r\n". $expected_output[2] ."\r\n". $expected_output[3];

        $expected_output_to_array = [
            ['id','仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイ"プ'],
            ['200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1"'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '"1'],
            ['100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '""1']
        ];
        
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
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

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]\n$expected_output[2]\n$expected_output[3]\n$expected_output[4]"; 
        $contents[] = $expected_output[0] ."\r". $expected_output[1] ."\r". $expected_output[2] ."\r". $expected_output[3] ."\r". $expected_output[4];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1] ."\r\n". $expected_output[2] ."\r\n". $expected_output[3]."\r\n". $expected_output[4];

        $expected_output_to_array = [
            ['"id','仕入先', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['"200', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['100"', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['""100', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['""yy"', 'Warthman', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1']
        ];
        
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
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

        $contents = [];
        $contents[] = $expected_output[0] . "\n$expected_output[1]\n$expected_output[2]\n$expected_output[3]\n$expected_output[4]"; 
        $contents[] = $expected_output[0] ."\r". $expected_output[1] ."\r". $expected_output[2] ."\r". $expected_output[3] ."\r". $expected_output[4];
        $contents[] = $expected_output[0] ."\r\n". $expected_output[1] ."\r\n". $expected_output[2] ."\r\n". $expected_output[3]."\r\n". $expected_output[4];

        $expected_output_to_array = [
            ['仕入先', '"id', 'PI No', 'SKU/パーツ', '仕入先コード', '原価通貨', '原価2', '原価', '個数', 'タイプ'],
            ['Warthman', '"200', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', '100"', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', '""100', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1'],
            ['Warthman', '""yy"', 'pi_import_test_3', 'testemptyspace', '199', '1', '84', '958.11', '15', '1']
        ];
        
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
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

        $contents = [];
        $contents[] = '"仕入'."\n".'先コード",仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ"'."\n".'"'."\n".'testemptyspace",Warthman,"200",pi_import_test_3,199,1,84,958.11,15,"1"'."\n".'"testemptyspace'."\n".'",Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1"'."\n".'"'."\n".'testemptyspace'."\n".'",Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1"'."\n".'"testempt'."\n".'yspace",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"'."\n".'"testempt'."\n"."\n".'            yspace",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"'."\n".'"'."\n".'testempt'."\n"."\n".'            yspace'."\n".'",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"';
        $contents[] = '"仕入'."\r".'先コード",仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ"'."\r".'"'."\r".'testemptyspace",Warthman,"200",pi_import_test_3,199,1,84,958.11,15,"1"'."\r".'"testemptyspace'."\r".'",Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1"'."\r".'"'."\r".'testemptyspace'."\r".'",Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1"'."\r".'"testempt'."\r".'yspace",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"'."\r".'"testempt'."\r"."\r".'            yspace",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"'."\r".'"'."\r".'testempt'."\r"."\r".'            yspace'."\n".'",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"';
        $contents[] = '"仕入'."\r\n".'先コード",仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ"'."\r\n".'"'."\r\n".'testemptyspace",Warthman,"200",pi_import_test_3,199,1,84,958.11,15,"1"'."\r\n".'"testemptyspace'."\r\n".'",Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1"'."\r\n".'"'."\r\n".'testemptyspace'."\r\n".'",Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1"'."\r\n".'"testempt'."\r\n".'yspace",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"'."\r\n".'"testempt'."\r\n"."\r\n".'            yspace",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"'."\r\n".'"'."\r\n".'testempt'."\r\n"."\r\n".'            yspace'."\r\n".'",Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1"';

        $expected_output_to_array = [
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

        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
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
        
        $contents = [];
        $contents[] = '仕入先,"id",PI No,SKU/パーツ,"仕入'."\n".'先コード",原価通貨,原価2,原価,個数,"タイプ"'."\n".'Warthman,"200",pi_import_test_3,"'."\n".'testemptyspace",199,1,84,958.11,15,"1"'."\n".'Warthman,"100",pi_import_test_3,"testemptyspace'."\n".'",199,1,84,958.11,15,"1"'."\n".'Warthman,"100",pi_import_test_3,"'."\n".'testemptyspace'."\n".'",199,1,84,958.11,15,"1"'."\n".'Warthman,"yy",pi_import_test_3,"testempt'."\n".'yspace",199,1,84,958.11,15,"1"'."\n".'Warthman,"yy",pi_import_test_3,"testempt'."\n"."\n".'            yspace",199,1,84,958.11,15,"1"'."\n".'Warthman,"yy",pi_import_test_3,"'."\n".'testempt'."\n"."\n".'            yspace'."\n".'",199,1,84,958.11,15,"1"';
        $contents[] = '仕入先,"id",PI No,SKU/パーツ,"仕入'."\r".'先コード",原価通貨,原価2,原価,個数,"タイプ"'."\r".'Warthman,"200",pi_import_test_3,"'."\r".'testemptyspace",199,1,84,958.11,15,"1"'."\r".'Warthman,"100",pi_import_test_3,"testemptyspace'."\r".'",199,1,84,958.11,15,"1"'."\r".'Warthman,"100",pi_import_test_3,"'."\r".'testemptyspace'."\r".'",199,1,84,958.11,15,"1"'."\r".'Warthman,"yy",pi_import_test_3,"testempt'."\r".'yspace",199,1,84,958.11,15,"1"'."\r".'Warthman,"yy",pi_import_test_3,"testempt'."\r"."\r".'            yspace",199,1,84,958.11,15,"1"'."\r".'Warthman,"yy",pi_import_test_3,"'."\r".'testempt'."\r"."\r".'            yspace'."\r".'",199,1,84,958.11,15,"1"';
        $contents[] = '仕入先,"id",PI No,SKU/パーツ,"仕入'."\r\n".'先コード",原価通貨,原価2,原価,個数,"タイプ"'."\r\n".'Warthman,"200",pi_import_test_3,"'."\r\n".'testemptyspace",199,1,84,958.11,15,"1"'."\r\n".'Warthman,"100",pi_import_test_3,"testemptyspace'."\r\n".'",199,1,84,958.11,15,"1"'."\r\n".'Warthman,"100",pi_import_test_3,"'."\r\n".'testemptyspace'."\r\n".'",199,1,84,958.11,15,"1"'."\r\n".'Warthman,"yy",pi_import_test_3,"testempt'."\r\n".'yspace",199,1,84,958.11,15,"1"'."\r\n".'Warthman,"yy",pi_import_test_3,"testempt'."\r\n"."\r\n".'            yspace",199,1,84,958.11,15,"1"'."\r\n".'Warthman,"yy",pi_import_test_3,"'."\r\n".'testempt'."\r\n"."\r\n".'            yspace'."\r\n".'",199,1,84,958.11,15,"1"';

        $expected_output_to_array = [
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

        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
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

        $contents = [];
        $contents[] = '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入'."\n".'先コード"'."\n".'Warthman,"200",pi_import_test_3,199,1,84,958.11,15,"1","'."\n".'testemptyspace"'."\n".'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace'."\n".'"'."\n".'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","'."\n".'testemptyspace'."\n".'"'."\n".'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\n".'yspace"'."\n".'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\n"."\n".'            yspace"'."\n".'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","'."\n".'testempt'."\n"."\n".'            yspace'."\n".'"';
        $contents[] = '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入'."\r".'先コード"'."\r".'Warthman,"200",pi_import_test_3,199,1,84,958.11,15,"1","'."\r".'testemptyspace"'."\n".'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace'."\r".'"'."\r".'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","'."\r".'testemptyspace'."\r".'"'."\r".'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\r".'yspace"'."\r".'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\r"."\r".'            yspace"'."\r".'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","'."\r".'testempt'."\r"."\r".'            yspace'."\r".'"';
        $contents[] = '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入'."\r\n".'先コード"'."\r\n".'Warthman,"200",pi_import_test_3,199,1,84,958.11,15,"1","'."\r\n".'testemptyspace"'."\r\n".'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace'."\r\n".'"'."\r\n".'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","'."\r\n".'testemptyspace'."\r\n".'"'."\r\n".'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\r\n".'yspace"'."\r\n".'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\r\n"."\r\n".'            yspace"'."\r\n".'Warthman,"yy",pi_import_test_3,199,1,84,958.11,15,"1","'."\r\n".'testempt'."\r\n"."\r\n".'            yspace'."\r\n".'"';

        $expected_output_to_array = [
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
        
        foreach ($contents as $content) {
            $parsed_result = Csv::parseCsvContentToArray($content);
            $this->assertEquals($expected_output, $parsed_result);
            $result = Csv::convertCsvLinesToArrayFormat($parsed_result);
            $this->assertEquals($expected_output_to_array, $result);
        }
    }

    public function testNewlineExistsContent()
    {
        $expected_output = [
            '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入\n先コード"',
            '"\nWarthman","200",pi_import_test_3,199,1,84,958.11,15,"1","\ntestemptyspace"',
            'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace\n"',
            '"   Warthman\n","100",pi_import_test_3,199,1,84,958.11,15,"1","\ntestemptyspace\n"',
            '"\n    Warthman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt\nyspace"',
            '"War\n\nthman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt\n\n            yspace"',
            'Warthman,"yy",pi_import_test_3,199,1,"8\n    4",958.11,15,"1","\ntestempt\n\n            yspace\n"',
        ];

        $contents = [];
        $contents[] = '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入'."\n".'先コード"'."\n".'"'."\n".'Warthman","200",pi_import_test_3,199,1,84,958.11,15,"1","'."\n".'testemptyspace"'."\n".'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace'."\n".'"'."\n".'"   Warthman'."\n".'","100",pi_import_test_3,199,1,84,958.11,15,"1","'."\n".'testemptyspace'."\n".'"'."\n".'"'."\n".'    Warthman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\n".'yspace"'."\n".'"War'."\n"."\n".'thman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\n"."\n".'            yspace"'."\n".'Warthman,"yy",pi_import_test_3,199,1,"8'."\n".'    4",958.11,15,"1","'."\n".'testempt'."\n"."\n".'            yspace'."\n".'"';
        $contents[] = '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入'."\r".'先コード"'."\r".'"'."\r".'Warthman","200",pi_import_test_3,199,1,84,958.11,15,"1","'."\r".'testemptyspace"'."\r".'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace'."\r".'"'."\r".'"   Warthman'."\r".'","100",pi_import_test_3,199,1,84,958.11,15,"1","'."\r".'testemptyspace'."\r".'"'."\r".'"'."\r".'    Warthman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\r".'yspace"'."\r".'"War'."\r"."\r".'thman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\r"."\r".'            yspace"'."\r".'Warthman,"yy",pi_import_test_3,199,1,"8'."\r".'    4",958.11,15,"1","'."\r".'testempt'."\r"."\r".'            yspace'."\r".'"';
        $contents[] = '仕入先,"id",PI No,SKU/パーツ,原価通貨,原価2,原価,個数,"タイプ","仕入'."\r\n".'先コード"'."\r\n".'"'."\r\n".'Warthman","200",pi_import_test_3,199,1,84,958.11,15,"1","'."\r\n".'testemptyspace"'."\r\n".'Warthman,"100",pi_import_test_3,199,1,84,958.11,15,"1","testemptyspace'."\r\n".'"'."\r\n".'"   Warthman'."\r\n".'","100",pi_import_test_3,199,1,84,958.11,15,"1","'."\r\n".'testemptyspace'."\r\n".'"'."\r\n".'"'."\r\n".'    Warthman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\r\n".'yspace"'."\r\n".'"War'."\r\n"."\r\n".'thman","yy",pi_import_test_3,199,1,84,958.11,15,"1","testempt'."\r\n"."\r\n".'            yspace"'."\r\n".'Warthman,"yy",pi_import_test_3,199,1,"8'."\r\n".'    4",958.11,15,"1","'."\r\n".'testempt'."\r\n"."\r\n".'            yspace'."\r\n".'"';


        $expected_output_to_array = [
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
        foreach ($contents as $content) {
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
            $this->assertEquals(count($expected_output_to_array), count($result));
            $this->assertEquals($expected_output_to_array[0], $result[0]);
            $this->assertEquals($expected_output_to_array[1], $result[1]);
            $this->assertEquals($expected_output_to_array[2], $result[2]);
            $this->assertEquals($expected_output_to_array[3], $result[3]);
            $this->assertEquals($expected_output_to_array[4], $result[4]);
            $this->assertEquals($expected_output_to_array[5], $result[5]);
            $this->assertEquals($expected_output_to_array[6], $result[6]);
        }
    }

    public function testBigContentData()
    {
        $file_path = 'unit-test/futureshop.csv';
        CloudStorage::setClient(getenv('STORAGE_KEY'), 'at-developer');
        $content = CloudStorage::readFromCloud($file_path);

        $parsed_result = Csv::parseCsvContentToArray($content);
        $this->assertEquals(51, count($parsed_result));
        $result = Csv::convertCsvLinesToArrayFormat($parsed_result);

        $this->assertEquals(51, count($result));
    }
}
