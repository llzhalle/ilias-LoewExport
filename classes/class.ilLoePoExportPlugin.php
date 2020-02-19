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
	/**
	 * @var ilObjTest
	 */
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
		switch($_POST['format'])
		{
			case ilLoePoExportPlugin::TYPE_EXCEL:
				$name_tmp = 'Löwenportal_Export';
				$name = "prf_".$_POST['lpnum']."";
				$suffix = 'xls';
				break;

			default:
				$name = 'Löwenportal_Export';
				$name = "prf_".$_POST['lpnum']."";
				$suffix = 'xls';
				break;
		}
		
		require_once('Modules/Test/classes/class.ilTestExportFilename.php');
		$filename = new ilTestExportFilename($this->ilObjTestData);
		
		$path = $filename->getPathname($suffix, $name);
		
		require_once $this->getDirectory(). '/lib/PHPExcel-1.8/Classes/PHPExcel.php';
		$excelObj = new PHPExcel();
		
		$this->prepairDataArea($worksheet = $excelObj->getActiveSheet());
		
		$this->prepairDataHeader($worksheet);
		
		$this->fillData($worksheet);

		
		
		$excelObj->setActiveSheetIndex(0);
		
		$writerObj = PHPExcel_IOFactory::createWriter($excelObj, 'Excel5');
		$writerObj->save($path);
		
		if (is_file($path))
		{
			ilUtil::deliverFile($path, $name.".".$suffix, '', false, true);
			ilUtil::sendSuccess(sprintf($this->txt('ilLoePoExport_export_written'), basename($path)), true);
		}
		else
		{
			ilUtil::sendFailure($this->plugin->txt('export_not_found'), true);
		}
	}
	
	/**
	 * Mark the corners of data area
	 * @param PHPExcel_Worksheet	$worksheet
	 */
	protected function prepairDataArea($worksheet)
	{
		$data = $this->ilObjTestData;
		
		/* Prepair data area for Löwenportal */
		$cell = $worksheet->getCell('A1');
		$cell->setValueExplicit('startHISsheet', PHPExcel_Cell_DataType::TYPE_STRING);
		
		$cell = $worksheet->getCell('H1');
		$cell->setValueExplicit('endHISsheet', PHPExcel_Cell_DataType::TYPE_STRING);		
		
		$cell = $worksheet->getCell('A'.(count($data->getParticipants())+4));
		$cell->setValueExplicit('endHISsheet', PHPExcel_Cell_DataType::TYPE_STRING);
		
		$worksheet->setTitle('test_results');
		$worksheet->setComments(array());
	}
	
	/**
	 * write legend header
	 * @param PHPExcel_Worksheet	$worksheet
	 */
	protected function prepairDataHeader($worksheet)
	{		
		/* Prepair data header for Löwenportal */
		$cell = $worksheet->getCell('A2');
		$cell->setValueExplicit('mtknr', PHPExcel_Cell_DataType::TYPE_STRING);
		$cell->getStyle()->applyFromArray(
				array(
					'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('rgb' => 'AAAAAA')
					)
				)
			);
		
		$cell = $worksheet->getCell('B2');
		$cell->setValueExplicit('nachname', PHPExcel_Cell_DataType::TYPE_STRING);
		$cell->getStyle()->applyFromArray(
				array(
						'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'AAAAAA')
						)
				)
			);
		
		$cell = $worksheet->getCell('C2');
		$cell->setValueExplicit('vorname', PHPExcel_Cell_DataType::TYPE_STRING);
		$cell->getStyle()->applyFromArray(
				array(
						'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'AAAAAA')
						)
				)
			);
		
		$cell = $worksheet->getCell('D2');
		$cell->setValueExplicit('bewertung', PHPExcel_Cell_DataType::TYPE_STRING);
		$cell->getStyle()->applyFromArray(
				array(
						'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'AAAAAA')
						)
				)
			);
		
		$cell = $worksheet->getCell('E2');
		$cell->setValueExplicit('pversuch', PHPExcel_Cell_DataType::TYPE_STRING);
		$cell->getStyle()->applyFromArray(
				array(
						'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'AAAAAA')
						)
				)
			);
		
		$cell = $worksheet->getCell('F2');
		$cell->setValueExplicit('pvermerk', PHPExcel_Cell_DataType::TYPE_STRING);
		$cell->getStyle()->applyFromArray(
				array(
						'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'AAAAAA')
						)
				)
			);
		
		$cell = $worksheet->getCell('G2');
		$cell->setValueExplicit('Studienprogramm', PHPExcel_Cell_DataType::TYPE_STRING);
		$cell->getStyle()->applyFromArray(
				array(
						'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'AAAAAA')
						)
				)
			);
		
		$cell = $worksheet->getCell('H2');
		$cell->setValueExplicit('email', PHPExcel_Cell_DataType::TYPE_STRING);
		$cell->getStyle()->applyFromArray(
				array(
						'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'AAAAAA')
						)
				)
			);
	}
	
	/**
	 * Fill the test overview sheet
	 * @param PHPExcel_Worksheet	$worksheet
	 */
	protected function fillData($worksheet)
	{
		$data = $this->ilObjTestData;
		
		$row = 3;
				
		foreach($data->getTestParticipants() as $id => $user)
		{
			$u = new ilObjUser($data->_getUserIdFromActiveId($id));

			$user_result = $data->getResultsForActiveId($id);
			
			$cell = $worksheet->getCell('A'.$row);
			$cell->setValueExplicit($user['matriculation'], PHPExcel_Cell_DataType::TYPE_STRING);
			
			$cell = $worksheet->getCell('B'.$row);
			$cell->setValueExplicit($user['lastname'], PHPExcel_Cell_DataType::TYPE_STRING);
			
			$cell = $worksheet->getCell('C'.$row);
			$cell->setValueExplicit($user['firstname'], PHPExcel_Cell_DataType::TYPE_STRING);
			
			$cell = $worksheet->getCell('D'.$row);
			$cell->setValueExplicit($user_result['mark_official'], PHPExcel_Cell_DataType::TYPE_STRING);
			
			$cell = $worksheet->getCell('E'.$row);
			$cell->setValueExplicit($user['tries'], PHPExcel_Cell_DataType::TYPE_STRING);
			
			$cell = $worksheet->getCell('G'.$row);
			$cell->setValueExplicit(1, PHPExcel_Cell_DataType::TYPE_STRING);
			
			$cell = $worksheet->getCell('H'.$row);
			$cell->setValueExplicit($u->getEmail(), PHPExcel_Cell_DataType::TYPE_STRING);
				
			$row++;
		}
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