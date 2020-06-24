<?php

namespace BlockParty\Test;

use BlockParty\DynamicBlock;
use BlockParty\CompactBlockWorksheet;
use PHPUnit\Framework\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

final class CompactBlockWorksheetTest extends TestCase
{
    /**
     * Get temporary file location for test
     *
     * @return string
     */
    private function getTempLocation()
    {
        $temp_dir = dirname(__FILE__) . '/tmp/';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir);
        }
        return $temp_dir;
    }

    /**
     * Test that an empty CompactBlockWorksheet produces correct output
     *
     * @return void
     */
    public function testSpreadsheetWithEmptyCompactBlockWorksheet()
    {
        $compact_block_worksheet = new CompactBlockWorksheet();
        $expected_coordinate_values = array();
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that a CompactBlockWorksheet with a single block produces correct output
     *
     * @return void
     */
    public function testSpreadsheetWithSingleCompactBlockWorksheet()
    {
        $dynamic_block = new DynamicBlock();
        $dynamic_block->addCell('B2', 'test');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow($dynamic_block);

        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, ['B2' => 'test'], __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that calling appendBlockToLastRow on empty worksheet is
     * the same as calling addBlockAsRow on an empty worksheet
     *
     * @return void
     */
    public function testAppendBlockAsColumnWorksOnEmptyCompactBlockWorksheet()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->AddCell('A1', 'block');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->appendBlockToLastRow($dynamic_block_1);

        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, ['A1' => 'block'], __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that a CompactBlockWorksheet with multiple blocks added to same row renders correctly
     *
     * @return void
     */
    public function testSpreadsheetWithMultipleBlocksInSingleRowOnCompactBlockWorksheet()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->addCell('B2', 'block 1');

        $dynamic_block_2 = new DynamicBlock();
        $dynamic_block_2->addCell('C4', 'block 2');

        $dynamic_block_3 = new DynamicBlock();
        $dynamic_block_3->addCell('A1', 'block 3');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->appendBlockToLastRow($dynamic_block_1)
                                ->appendBlockToLastRow($dynamic_block_2)
                                ->appendBlockToLastRow($dynamic_block_3);

        $expected_coordinate_values = array('B2' => 'block 1',
                                            'E4' => 'block 2',
                                            'F1' => 'block 3');
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that a CompactBlockWorksheet with multiple blocks added as separate rows renders correctly
     *
     * @return void
     */
    public function testSpreadsheetWithMultipleBlocksInSingleColumnOnCompactBlockWorksheet()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->addCell('B5', 'block 1');

        $dynamic_block_2 = new DynamicBlock();
        $dynamic_block_2->addCell('C4', 'block 2');

        $dynamic_block_3 = new DynamicBlock();
        $dynamic_block_3->addCell('A3', 'block 3');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow($dynamic_block_1)
                                ->addBlockAsRow($dynamic_block_2)
                                ->addBlockAsRow($dynamic_block_3);

        $expected_coordinate_values = array('B5' => 'block 1',
                                            'C9' => 'block 2',
                                            'A12' => 'block 3');
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that appendBlockToRow throws an exception when the supplied row is zero
     *
     * @return void
     */
    public function testAppendBlockToRowThrowsExceptionWhenSuppliedRowIsZero()
    {
        $compact_block_worksheet = new CompactBlockWorksheet();

        $this->expectException(\InvalidArgumentException::class);
        $compact_block_worksheet->appendBlockToRow(new DynamicBlock(), 0);
    }

    /**
     * Test that appendBlockToRow throws an exception when the supplied row is a negative value
     *
     * @return void
     */
    public function testAppendBlockToRowThrowsExceptionWhenSuppliedRowIsNegativeValue()
    {
        $compact_block_worksheet = new CompactBlockWorksheet();

        $this->expectException(\InvalidArgumentException::class);
        $compact_block_worksheet->appendBlockToRow(new DynamicBlock(), -1);
    }

    /**
     * Test that appendBlockToRow throws an exception when the supplied
     * row is greater than the number of existing rows
     *
     * @return void
     */
    public function testAppendBlockToRowThrowsExceptionWhenSuppliedRowDoesNotExist()
    {
        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow(new DynamicBlock())
                                ->addBlockAsRow(new DynamicBlock());

        $this->expectException(\InvalidArgumentException::class);
        $compact_block_worksheet->appendBlockToRow(new DynamicBlock(), 3);
    }

    /**
     * Test that a CompactBlockWorksheet with multiple blocks added as rows and columns
     * renders correctly, also test the CompactBlockWorksheet::appendBlockToRow method
     *
     * @return void
     */
    public function testSpreadsheetWithMultipleRowsAndColumnsOnCompactBlockWorksheet()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->addCell('B5', 'block 1');

        $dynamic_block_2 = new DynamicBlock();
        $dynamic_block_2->addCell('C4', 'block 2');

        $dynamic_block_3 = new DynamicBlock();
        $dynamic_block_3->addCell('A3', 'block 3');

        $dynamic_block_4 = new DynamicBlock();
        $dynamic_block_4->addCell('D2', 'block 4');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow($dynamic_block_1)
                                ->addBlockAsRow($dynamic_block_2)
                                ->appendBlockToRow($dynamic_block_3, 1)
                                ->appendBlockToRow($dynamic_block_4, 2);

        $expected_coordinate_values = array('B5' => 'block 1',
                                            'C9' => 'block 2',
                                            'C3' => 'block 3',
                                            'G7' => 'block 4');
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that the CompactBlockWorksheet::insertBlockAfterRow correctly inserts row correctly
     *
     * @return void
     */
    public function testInsertBlockAfterRowWhenSuppliedRowIsBetweenTwoRows()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->addCell('B2', 'block 1');

        $dynamic_block_2 = new DynamicBlock();
        $dynamic_block_2->addCell('C3', 'block 2');

        $dynamic_block_3 = new DynamicBlock();
        $dynamic_block_3->addCell('D4', 'block 3');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow($dynamic_block_1)
                                ->addBlockAsRow($dynamic_block_2)
                                ->insertBlockAfterRow($dynamic_block_3, 1);

        $expected_coordinate_values = array('B2' => 'block 1',
                                            'C9' => 'block 2',
                                            'D6' => 'block 3');
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

     /**
     * Test that appendBlockToRow inserts row as first row the supplied row number is zero
     *
     * @return void
     */
    public function testInserBlockAfterRowWhenSuppliedRowIsZero()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->addCell('C1', 'block 1');

        $dynamic_block_2 = new DynamicBlock();
        $dynamic_block_2->addCell('E3', 'block 2');

        $dynamic_block_3 = new DynamicBlock();
        $dynamic_block_3->addCell('B4', 'block 3');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow($dynamic_block_1)
                                ->addBlockAsRow($dynamic_block_2)
                                ->insertBlockAfterRow($dynamic_block_3, 0);

        $expected_coordinate_values = array('C5' => 'block 1',
                                            'E8' => 'block 2',
                                            'B4' => 'block 3');
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that appendBlockToRow inserts row as first row the supplied row number is last row number
     *
     * @return void
     */
    public function testInserBlockAfterRowWhenSuppliedRowIsLastRow()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->addCell('E9', 'block 1');

        $dynamic_block_2 = new DynamicBlock();
        $dynamic_block_2->addCell('D4', 'block 2');

        $dynamic_block_3 = new DynamicBlock();
        $dynamic_block_3->addCell('C7', 'block 3');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow($dynamic_block_1)
                                ->addBlockAsRow($dynamic_block_2)
                                ->insertBlockAfterRow($dynamic_block_3, 2);

        $expected_coordinate_values = array('E9' => 'block 1',
                                            'D13' => 'block 2',
                                            'C20' => 'block 3');
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that insertBlockAfterRow throws an exception when the supplied
     * row is a negative value
     *
     * @return void
     */
    public function testInsertBlockAfterRowThrowsExceptionWhenSuppliedRowIsNegativeValue()
    {
        $compact_block_worksheet = new CompactBlockWorksheet();

        $this->expectException(\InvalidArgumentException::class);
        $compact_block_worksheet->insertBlockAfterRow(new DynamicBlock(), -1);
    }

    /**
     * Test that insertBlockAfterRow throws an exception when the supplied
     * row is a greater than the number of existing rows
     *
     * @return void
     */
    public function testInsertBlockAfterRowThrowsExceptionWhenSuppliedRowDoesNotExist()
    {
        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow(new DynamicBlock())
                                ->addBlockAsRow(new DynamicBlock());

        $this->expectException(\InvalidArgumentException::class);
        $compact_block_worksheet->insertBlockAfterRow(new DynamicBlock(), 3);
    }


    /**
     * Assert that a BlockWorksheet produces specific values when saved on a spreadsheet
     *
     * @param  BlockWorksheet $worksheet
     * @param  Iterable       $expected_coordinate_values
     * @param  string         $temp_file_name
     *
     * @return void
     */
    private function assertBlockWorksheetProducesExepectedResults($worksheet, $expected_coordinate_values, $temp_file_name)
    {
        $file_location = $this->getTempLocation() . $temp_file_name;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->disconnectWorksheets();
        $spreadsheet->addSheet($worksheet);

        $xlsx_writer = new XlsxWriter($spreadsheet);
        $xlsx_writer->save($file_location);

        $xlsx_reader = new XlsxReader();
        $rendered_spreadsheet = $xlsx_reader->load($file_location);

        $rendered_worksheet = $rendered_spreadsheet->getSheet(0);
        $rendered_cells = $rendered_worksheet->getCellCollection();

        $missing_cells = array();
        $wrong_value_cells = array();
        $extra_coordinates = array();
        // Check that all corrdinates match our expected values
        foreach ($expected_coordinate_values as $coordinate => $expected_value) {
            if (!$rendered_cells->has($coordinate)) {
                $missing_cells[$coordinate] = $expected_value;
            } elseif (($actual_value = $rendered_cells->get($coordinate)->getValue()) != $expected_value) {
                $wrong_value_cells[$coordinate] = ['expected' => $expected_value, 'actual' => $actual_value];
            }
        }
        // Check if there are any unexpected coordinates
        foreach (array_diff($rendered_cells->getCoordinates(), array_keys($expected_coordinate_values)) as $extra_coordinate) {
            $extra_coordinates[$extra_coordinate] = $rendered_cells->get($extra_coordinate)->getValue();
        }

        if ($missing_cells || $wrong_value_cells || $extra_coordinates) {
            $error_message = 'Rendered XLSX file did not match expected results:' . PHP_EOL;
            foreach ($missing_cells as $coordinate => $value) {
                $error_message .= '  -  ';
                $error_message .= "Missing expected value '${value}' at coordinate ${coordinate}" . PHP_EOL;
            }
            foreach ($wrong_value_cells as $coordinate => $values) {
                $error_message .= '  -  ';
                $error_message .= "Expected value '${values['expected']}' for coordinate ${coordinate} but actual value was '${values['actual']}'" . PHP_EOL;
            }
            foreach ($extra_coordinates as $coordinate => $value) {
                $error_message .= '  -  ';
                $error_message .= "Expected coordinate ${coordinate} to not be set but had value of '${value}'" . PHP_EOL;
            }
            $error_message .= "Failed XLSX file viewable at: '${file_location}'";
            $this->fail($error_message);
        }
        unlink($file_location);
        $this->assertTrue(true);
    }
}
