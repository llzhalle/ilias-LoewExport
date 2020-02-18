<?php

include_once("./Services/Component/classes/class.ilPlugin.php");
	
/**
* Question plugin Infotext
*
* @author Christoph Jobst <christoph.jobst@llz.uni-halle.de>
* @version $Id$
* @ingroup ModulesTestQuestionPool
*/
class ilLoePoExportPlugin extends ilPlugin
{
	private $ilObjTestData;
	
	const TYPE_EXCEL = 'excel';

	final function getPluginName()
	{
		return "LoePoExport";
	}
	
	/**
	 * Get Component Type
	 *
	 * @return        string        Component Type
	 */
	final function getComponentType()
	{
		return IL_COMP_MODULE;
	}
	
	/**
	 * Get Component Name.
	 *
	 * @return        string        Component Name
	 */
	final function getComponentName()
	{
		return "Test";
	}
	
	/**
	 * Get Slot Name.
	 *
	 * @return        string        Slot Name
	 */
	final function getSlot()
	{
		return "Export";
	}
	
	/**
	 * Get Slot ID.
	 *
	 * @return        string        Slot Id
	 */
	final function getSlotId()
	{
		return "texp";
	}
	
	/**
	 * Object initialization done by slot.
	 */
	protected final function slotInit()
	{
		// nothing to do here
	}
	
	function getFormat()
	{
		return ilLoePoExportPlugin::TYPE_EXCEL;
	}
	
	function getFormatLabel()
	{
		return $this->txt('ilLoePoExport_Label');
	}

	function setTest($obj)
	{
		$this->ilObjTestData = $obj;
		
		global $tpl;
		
		/* inject JS */
		$tpl->addJavaScript($this->getDirectory().'/templates/loepoexp.js');
	}
	
	function export()
	{
		$this->includeClass("class.ilLoePoExport.php");
				
		switch($_POST['format'])
		{
			case ilLoePoExportPlugin::TYPE_EXCEL:
				$name = 'Löwenportal_Export';
				$suffix = 'xls';
				$type = ilLoePoExport::TYPE_EXCEL;
				break;

			default:
				$name = 'Löwenportal_Export';
				$suffix = 'xls';
				$type = ilLoePoExport::TYPE_EXCEL;
				break;
		}
		
		require_once('Modules/Test/classes/class.ilTestExportFilename.php');
		$filename = new ilTestExportFilename($this->ilObjTestData);
		
		$path = $filename->getPathname($suffix, $name);
		
		require_once $this->getDirectory(). '/lib/PHPExcel-1.8/Classes/PHPExcel.php';
		$excelObj = new PHPExcel();
		
		$this->fillData($worksheet = $excelObj->getActiveSheet());

		$cell = $worksheet->getCell('A1');
		$cell->setValueExplicit('blubb', PHPExcel_Cell_DataType::TYPE_STRING);
		
		$worksheet->setTitle('test_results');
		$worksheet->setComments(array());
		
		$excelObj->setActiveSheetIndex(0);
		
		$writerObj = PHPExcel_IOFactory::createWriter($excelObj, 'Excel5');
		$writerObj->save($path);
		
		if (is_file($path))
		{
			ilUtil::deliverFile($path, $_POST['pruefungsnummer'].'_klu.xls', '', false, true);
			ilUtil::sendSuccess(sprintf($this->txt('ilLoePoExport_export_written'), basename($path)), true);
		}
		else
		{
			ilUtil::sendFailure($this->plugin->txt('export_not_found'), true);
		}
	}
	
	/**
	 * Fill the test overview sheet
	 * @param PHPExcel_Worksheet	$worksheet
	 */
	protected function fillData($worksheet)
	{
		$data = $this->ilObjTestData;
		
		
		
		
		
		return false;
		$data = array();
		/** @var ilExteStatValue[]  $values */
		$values = $this->statObj->getSourceData()->getBasicTestValues();
		foreach ($this->statObj->getSourceData()->getBasicTestValuesList() as $def)
		{
			array_push($data,
					array(
							'title' => $def['title'],
							'description' => $def['description'],
							'value' => $values[$def['id']],
							'details' => null
					));
		}
		
		/** @var  ilExteEvalTest $evaluation */
		foreach ($this->statObj->getEvaluations(
				ilExtendedTestStatistics::LEVEL_TEST,
				ilExtendedTestStatistics::PROVIDES_VALUE) as $class => $evaluation)
		{
			array_push($data,
					array(
							'title' => $evaluation->getTitle(),
							'description' => $evaluation->getDescription(),
							'value' => $evaluation->getValue()
					));
		}
		
		// Debug value formats
		if ($this->plugin->debugFormats())
		{
			foreach (ilExteStatValue::_getDemoValues() as $value)
			{
				array_push($data,
						array(
								'title' => $value->comment,
								'description' => '',
								'value' => $value,
						));
			}
		}
		
		$rownum = 0;
		$comments = array();
		foreach ($data as $row)
		{
			$rownum++;
			
			// title
			$cell = $worksheet->getCell('A'.$rownum);
			$cell->setValueExplicit($row['title'],PHPExcel_Cell_DataType::TYPE_STRING);
			$cell->getStyle()->applyFromArray($this->headerStyle);
			if (!empty($row['description']))
			{
				$comments['A'.$rownum] = ilExteStatValueExcel::_createComment($row['description']);
			}
			
			/** @var ilExteStatValue $value */
			$value = $row['value'];
			$cell = $worksheet->getCell('B'.$rownum);
			$cell->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->valView->writeInCell($cell, $value);
			if (!empty($value->comment))
			{
				$comments['B'.$rownum] = $this->valView->getComment($value);
			}
		}
		
		$worksheet->setTitle($this->plugin->txt('test_results'));
		$worksheet->setComments($comments);
		$this->adjustSizes($worksheet);
	}
	
	/**
	 * @param PHPExcel_Worksheet	$worksheet
	 */
	protected function adjustSizes($worksheet, $range = null)
	{
		$range = isset($range) ? $range : range('A', $worksheet->getHighestColumn());
		foreach ($range as $columnID)
		{
			$worksheet->getColumnDimension($columnID)->setAutoSize(true);
		}
	}
}
?>