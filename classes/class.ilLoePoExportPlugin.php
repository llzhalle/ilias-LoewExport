<?php

include_once("./Services/Component/classes/class.ilPlugin.php");
	
use ILIAS\Filesystem\Util\LegacyPathHelper;

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
	
	private $loeMarkSchema = null;
	
	private $loeMarkField = 'mark';
	
	private $loeMarkMapping = array(
			"simple" => array(
				"1" => "++",
				"0" => "--",
			),
			"normal" => array(
				"0" => 500,
				"1.0" => 100,
				"1.1" => 110,
				"1.2" => 120,
				"1.3" => 130,
				"1.4" => 140,
				"1.5" => 150,
				"1.6" => 160,
				"1.7" => 170,
				"1.8" => 180,
				"1.9" => 190,
				"2.0" => 200,
				"2.1" => 210,
				"2.2" => 220,
				"2.3" => 230,
				"2.4" => 240,
				"2.5" => 250,
				"2.6" => 260,
				"2.7" => 270,
				"2.8" => 280,
				"2.9" => 290,
				"3.0" => 300,
				"3.1" => 310,
				"3.2" => 320,
				"3.3" => 330,
				"3.4" => 340,
				"3.5" => 350,
				"3.6" => 360,
				"3.7" => 370,
				"3.8" => 380,
				"3.9" => 390,
				"4.0" => 400,
// 				"4.1" => 410,
// 				"4.2" => 420,
// 				"4.3" => 430,
// 				"4.4" => 440,
// 				"4.5" => 450,
// 				"4.6" => 460,
// 				"4.7" => 470,
// 				"4.8" => 480,
// 				"4.9" => 490,
			),
			"wiwi" => array(
				"0" => "-P",
				/* Passthrough */
			),
			"law" => array(
				"0" => "-P",
				/* Passthrough */
			),
	);
	
	const TYPE_EXCEL = 'excel';
	const TYPE_OUTPUT = 'short';
	
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
		$tpl->addJavaScript($this->getDirectory().'/templates/loepoexp_lang.js');
		$tpl->addJavaScript($this->getDirectory().'/templates/loepoexp.js');
	}
	
	/**
	 * build the export file, save, deliver and remove it
	 */
	function export()
	{		
		global $ilCtrl;
		
		switch($_POST['format'])
		{
			case ilLoePoExportPlugin::TYPE_EXCEL:
				$name_tmp = 'Löwenportal_Export';
				$name = "prf_".$_POST['lpnum']."_".$_POST['lpsem']."_".$_POST['lpdate']."";
				$suffix = 'xls';
				break;

			default:
				$name = 'Löwenportal_Export';
				$name = "prf_".$_POST['lpnum']."_".$_POST['lpsem']."_".$_POST['lpdate']."";
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
		
		try {
			$this->fillData($worksheet);
		} catch (Exception $e) {
			ilUtil::sendFailure($this->txt($e->getMessage()), true);
			$ilCtrl->redirectByClass('iltestexportgui');
			return;
		}

		$excelObj->setActiveSheetIndex(0);
		
		$this->adjustSizes($worksheet);
		
		if(is_dir(dirname($path)) === false)
		{
			global $DIC;
			
			$DIC->filesystem()->storage()->createDir(LegacyPathHelper::createRelativePath(dirname($path)));
		}
				
		$writerObj = PHPExcel_IOFactory::createWriter($excelObj, 'Excel5');
		
		$writerObj->save($path);

		if(is_file($path) === true)
		{
			ilUtil::deliverFile($path, $name.".".$suffix, '', false, true, false);
			ilUtil::sendSuccess(sprintf($this->txt('ilLoePoExport_export_written'), basename($path)), true);
			$ilCtrl->redirectByClass('iltestexportgui');
		}
		else
		{
			ilUtil::sendFailure(sprintf($this->txt('ilLoePoExport_export_not_found'), basename($path)), true);
		}
	}
	
	/**
	 * Mark the corners of data area
	 * @param PHPExcel_Worksheet	$worksheet
	 * @param ilLoePoExportPlugin::TYPE_OUTPUT 	$exportType
	 */
	protected function prepairDataArea($worksheet, $exportType = ilLoePoExportPlugin::TYPE_OUTPUT)
	{
		$data = $this->ilObjTestData;
		
		/* Prepair data area for Löwenportal */
		$cell = $worksheet->getCell('A1');
		$cell->setValueExplicit('startHISsheet', PHPExcel_Cell_DataType::TYPE_STRING);
		
		$cell = $worksheet->getCell($exportType === 'short' ? 'B1' : 'H1');
		$cell->setValueExplicit('endHISsheet', PHPExcel_Cell_DataType::TYPE_STRING);		
		
		$cell = $worksheet->getCell('A'.(count($data->getParticipants())+4));
		$cell->setValueExplicit('endHISsheet', PHPExcel_Cell_DataType::TYPE_STRING);
		
		$worksheet->setTitle('First Sheet');
		$worksheet->setComments(array());
	}
	
	/**
	 * write legend header
	 * @param PHPExcel_Worksheet	$worksheet
	 * @param ilLoePoExportPlugin::TYPE_OUTPUT 	$exportType
	 */
	protected function prepairDataHeader($worksheet, $exportType = ilLoePoExportPlugin::TYPE_OUTPUT)
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
		
		if($exportType === 'short')
		{
			$cell = $worksheet->getCell('B2');
			$cell->setValueExplicit('bewertung', PHPExcel_Cell_DataType::TYPE_STRING);
			$cell->getStyle()->applyFromArray(
					array(
							'fill' => array(
									'type' => PHPExcel_Style_Fill::FILL_SOLID,
									'color' => array('rgb' => 'AAAAAA')
							)
					)
				);
		}
		else 
		{
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
	}
	
	/**
	 * Fill the test data to sheet
	 * @param PHPExcel_Worksheet	$worksheet
	 * @param ilLoePoExportPlugin::TYPE_OUTPUT 	$exportType
	 */
	private function fillData($worksheet, $exportType = ilLoePoExportPlugin::TYPE_OUTPUT)
	{
		if($this->loeMarkSchema === null) {
			try {
				$this->detectMarkSchema();
			} catch (Exception $e) {
				throw $e;
			}
		}
		
		$data = $this->ilObjTestData;
				
		$row = 2;
				
		foreach($data->getTestParticipants() as $id => $user)
		{
			$row++;
			
			$u = new ilObjUser($data->_getUserIdFromActiveId($id));

			$user_result = $data->getResultsForActiveId($id);
			
			if(empty($user['matriculation']) === true) {
				
				ilUtil::sendInfo('error matrikel', true);
				
				continue;
			}
			
			$cell = $worksheet->getCell('A'.$row);
			$cell->setValueExplicit($user['matriculation'], PHPExcel_Cell_DataType::TYPE_STRING);
			
			if($exportType === 'short')
			{
				$cell = $worksheet->getCell('B'.$row);
				$cell->setValueExplicit($this->getFilteredMark($user_result), PHPExcel_Cell_DataType::TYPE_STRING);
			}
			else 
			{
				$cell = $worksheet->getCell('B'.$row);
				$cell->setValueExplicit($user['lastname'], PHPExcel_Cell_DataType::TYPE_STRING);
				
				$cell = $worksheet->getCell('C'.$row);
				$cell->setValueExplicit($user['firstname'], PHPExcel_Cell_DataType::TYPE_STRING);
				
				$cell = $worksheet->getCell('D'.$row);
				$cell->setValueExplicit($this->getFilteredMark($user_result), PHPExcel_Cell_DataType::TYPE_STRING);
				
				$cell = $worksheet->getCell('E'.$row);
				$cell->setValueExplicit($user['tries'], PHPExcel_Cell_DataType::TYPE_STRING);
				
				$cell = $worksheet->getCell('G'.$row);
				$cell->setValueExplicit(1, PHPExcel_Cell_DataType::TYPE_STRING);
				
				$cell = $worksheet->getCell('H'.$row);
				$cell->setValueExplicit($u->getEmail(), PHPExcel_Cell_DataType::TYPE_STRING);
			}
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
	
	private function detectMarkSchema()
	{
		$data = $this->ilObjTestData;
		
		$schema = $data->getMarkSchema();
		
		if(is_array($schema->mark_steps) && count($schema->mark_steps) === 2 &&
		   ($schema->mark_steps[0]->passed === "" && $schema->mark_steps[1]->passed === "1" || $schema->mark_steps[0]->passed === "1" && $schema->mark_steps[1]->passed === "")
		) {
			$this->loeMarkSchema = "simple";
		}
		else 
		{
			$mark_short = $mark_official = array();
			
			foreach($schema->mark_steps as $key => $step)
			{
				if($step->passed === "")
				{
					continue;
				}
				
				$mark_tmp = $mark_official_tmp = array();
				
				/* get number from string, if there is any */
				preg_match_all('!\d+(?:\.\d+)?!', str_replace(",", ".", $step->short_name), $mark_tmp);
				preg_match_all('!\d+(?:\.\d+)?!', str_replace(",", ".", $step->official_name), $mark_official_tmp);
				
				if(empty($mark_tmp[0]) === false)
				{
					if(count($mark_tmp[0]) === 1)
					{
						$mark_short[$key] = $mark_tmp[0][0];
					}
					else 
					{
						throw new Exception("ilLoePoExport_mark_error");
					}
				}
				
				if(empty($mark_official_tmp[0]) === false)
				{
					if(count($mark_official_tmp[0]) === 1)
					{
						if(isset($mark_short[$key]) === false || $mark_short[$key] === $mark_official_tmp[0][0] || floatval($mark_short[$key]) === floatval($mark_official_tmp[0][0]))
						{
							$mark_official[$key] = $mark_official_tmp[0][0];
						}
						else
						{
							throw new Exception("ilLoePoExport_mark_error");
						}
					}
					else
					{
						throw new Exception("ilLoePoExport_mark_error");
					}
				}
				
				if(empty($mark_short[$key]) === true && empty($mark_official[$key]) === true && $step->passed === "1")
				{
					throw new Exception("ilLoePoExport_mark_error");
				}
			}
			
			/* get field that fits */
			if(empty($mark_short) === true && empty($mark_official) === false)
			{
				$this->loeMarkField = "mark_official";
			}
			else if(empty($mark_short) === false && empty($mark_official) === true)
			{
				$this->loeMarkField = "mark_short";
			}
			else if(empty($mark_short) === false && empty($mark_official) === false)
			{
				if(array_keys($mark_short) === array_keys($mark_official))
				{
					$this->loeMarkField = "mark_official";
				}
				else 
				{
					throw new Exception("ilLoePoExport_mark_error");
				}
			}
			else 
			{
				throw new Exception("ilLoePoExport_mark_error");
			}
			
			/* check if its floats */
			$isFloat = false;
			
			foreach(${$this->loeMarkField} as $num)
			{
				if(filter_var($num, FILTER_VALIDATE_FLOAT) !== false)
				{
					$isFloat = true;
				}
			}
			
			/* check for normal marks */
			if($isFloat === true || max(${$this->loeMarkField}) <= 6)
			{
				$this->loeMarkSchema = 'normal';
			}
			
			/* check for Law */
			if($isFloat === false || max(${$this->loeMarkField}) >= 6 && max(${$this->loeMarkField}) <= 18)
			{
				$this->loeMarkSchema = 'law';
			}
			
			/* check for WiWi */
			if($isFloat === false || max(${$this->loeMarkField}) >= 19 && max(${$this->loeMarkField}) <= 100)
			{
				$this->loeMarkSchema = 'wiwi';
			}
		}
	}
	
	/**
	 * 
	 * @param array $user_result
	 * @return string
	 */
	private function getFilteredMark($user_result = array())
	{		
		$mark = array();
		
		/* get number from string, if there is any */
		preg_match_all('!\d+(?:\.\d+)?!', str_replace(",", ".", $user_result[$this->loeMarkField]), $mark);
		
		if($user_result['passed'] === "0" && $user_result['pass'] === "0" && $user_result['failed'] === "1")
		{
			return $this->loeMarkMapping[$this->loeMarkSchema][0] ?? $mark[0][0];
		}
		else {
			return $this->loeMarkMapping[$this->loeMarkSchema][$mark[0][0]] ?? $mark[0][0];
		}

		return $user_result[$this->loeMarkField];
	}
}
?>