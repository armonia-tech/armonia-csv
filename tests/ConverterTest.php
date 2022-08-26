<?php
namespace ArmoniaCsv\tests;

use PHPUnit\Framework\TestCase;
use ArmoniaCsv\Converter;

class ConverterTest extends TestCase
{
    private string $_test_data_dir;
    private string $_test_tmp_dir;
    public function setUp() : void
    {
        $this->_test_data_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'file'. DIRECTORY_SEPARATOR;
        $this->_test_tmp_dir  = $this->_test_data_dir . 'tmp'. DIRECTORY_SEPARATOR;

        if (is_dir($this->_test_tmp_dir) === false) {
            mkdir($this->_test_tmp_dir);
        }
    }

    public function tearDown() : void
    {
        array_map('unlink', glob($this->_test_tmp_dir.'*.*'));
        rmdir($this->_test_tmp_dir);
    }


    public function testDefault()
    {
        $source_file = $this->_test_tmp_dir . 'testDefault.csv';
        copy($this->_test_data_dir . 'test-utf-8.csv',  $source_file);

        $converter = new Converter();
        $return_file_name = $converter->toXlsx($source_file);

        $expected_file = $this->_test_tmp_dir . 'testDefault.xlsx';

        $this->assertEquals($expected_file, $return_file_name);
        $this->assertFileExists($expected_file);
    }

    public function testSourceEncodingSJIS()
    {
        $source_file = $this->_test_tmp_dir . 'testSourceEncodingSJIS.csv';
        copy($this->_test_data_dir . 'test-sjis.csv',  $source_file);

        $converter = new Converter();
        $converter->toXlsx($source_file, 'SJIS');

        $expected_file = $this->_test_tmp_dir . 'testSourceEncodingSJIS.xlsx';

        $this->assertFileExists($expected_file);
    }

    public function testDisableAutoresizeColumn()
    {
        $source_file = $this->_test_tmp_dir . 'testDisableAutoresizeColumn.csv';
        copy($this->_test_data_dir . 'test-utf-8.csv',  $source_file);

        $converter = new Converter();
        $converter->toXlsx($source_file, 'UTF-8', false, false);

        $expected_file = $this->_test_tmp_dir . 'testDisableAutoresizeColumn.xlsx';

        $this->assertFileNotExists($source_file);
        $this->assertFileExists($expected_file);
    }

    public function testRemoveSourceFile()
    {
        $source_file = $this->_test_tmp_dir . 'testRemoveSourceFile.csv';
        copy($this->_test_data_dir . 'test-utf-8.csv',  $source_file);

        $converter = new Converter();
        $converter->toXlsx($source_file, 'UTF-8', false);

        $expected_file = $this->_test_tmp_dir . 'testRemoveSourceFile.xlsx';

        $this->assertFileNotExists($source_file);
        $this->assertFileExists($expected_file);
    }

    public function testAutoExtension()
    {
        $source_file = $this->_test_tmp_dir . 'testAutoExtension.txt';
        copy($this->_test_data_dir . 'test-utf-8.csv',  $source_file);

        $converter = new Converter();
        $auto_filename = $converter->toXlsx($source_file, 'UTF-8', false);

        $expected_file = $this->_test_tmp_dir . 'testAutoExtension.txt.xlsx';

        $this->assertEquals($expected_file, $auto_filename);
        $this->assertFileNotExists($source_file);
        $this->assertFileExists($expected_file);
    }

    public function testFileNotExist()
    {
        $source_file = $this->_test_tmp_dir . 'testFileNotExist.csv';

        $this->expectException('PhpOffice\PhpSpreadsheet\Reader\Exception');
        $this->expectExceptionMessage($source_file . ' is an Invalid Spreadsheet file.');
        $this->expectExceptionMessageMatches('/ is an Invalid Spreadsheet file./');

        $converter = new Converter();
        $converter->toXlsx($source_file);
    }

    public function testNoWritePermission()
    {
        // Warning: This function must be run as a non root user to pass the test
        $writable = is_writable('/etc');

        $this->assertFalse($writable, 'This function must be run as a non root user to pass this test');

        $this->expectError();
        $this->expectErrorMessage('fopen(/etc/timezone.xlsx): failed to open stream: Permission denied');

        $source_file = '/etc/timezone';

        $converter = new Converter();
        $converter->toXlsx($source_file);

    }
}
