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
		private $test;
	
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
			return "test";
		}
		
		function getFormatLabel()
		{
			return $this->txt('assExport_Label');
		}

		function setTest($obj)
		{
			$this->test = $obj;
		}
		
		function export()
		{
			$a_id = $this->test->test_id;
			
			return array(
					"success" => $success,
					"file" => $new_file,
					"directory" => $export_dir
			);
			
			$this->log->debug("export type: $a_type, id: $a_id, target_release: ".$a_target_release);
			
			// if no target release specified, use latest major release number
			if ($a_target_release == "")
			{
				$v = explode(".", ILIAS_VERSION_NUMERIC);
				$a_target_release = $v[0].".".$v[1].".0";
				$this->log->debug("target_release set to: ".$a_target_release);
			}
			
			// manifest writer
			include_once "./Services/Xml/classes/class.ilXmlWriter.php";
			$this->manifest_writer = new ilXmlWriter();
			$this->manifest_writer->xmlHeader();
			$this->manifest_writer->xmlStartTag(
					'Manifest',
					array(
							"MainEntity" => $a_type,
							"Title" => ilObject::_lookupTitle($a_id),
							"TargetRelease" => $a_target_release,
							"InstallationId" => IL_INST_ID,
							"InstallationUrl" => ILIAS_HTTP_PATH));
					
					// get export class
					ilExport::_createExportDirectory($a_id, "xml", $a_type);
					$export_dir = ilExport::_getExportDirectory($a_id, "xml", $a_type);
					$ts = time();
					
					// Workaround for test assessment
					$sub_dir = $ts.'__'.IL_INST_ID.'__'.$a_type.'_'.$a_id;
					$new_file = $sub_dir.'.zip';
					
					$this->export_run_dir = $export_dir."/".$sub_dir;
					ilUtil::makeDirParents($this->export_run_dir);
					$this->log->debug("export dir: ".$this->export_run_dir);
					
					$this->cnt = array();
					
					include_once './Services/Export/classes/class.ilImportExportFactory.php';
					$class = ilImportExportFactory::getExporterClass($a_type);
					$comp = ilImportExportFactory::getComponentForExport($a_type);
					
					$success = $this->processExporter($comp, $class, $a_type, $a_target_release, $a_id);
					
					$this->manifest_writer->xmlEndTag('Manifest');
					
					$this->manifest_writer->xmlDumpFile($this->export_run_dir."/manifest.xml", false);
					
					// zip the file
					$this->log->debug("zip: ".$export_dir."/".$new_file);
					ilUtil::zip($this->export_run_dir, $export_dir."/".$new_file);
					ilUtil::delDir($this->export_run_dir);
					
					// Store info about export
					if($success)
					{
						include_once './Services/Export/classes/class.ilExportFileInfo.php';
						$exp = new ilExportFileInfo($a_id);
						$exp->setVersion($a_target_release);
						$exp->setCreationDate(new ilDateTime($ts,IL_CAL_UNIX));
						$exp->setExportType('xml');
						$exp->setFilename($new_file);
						$exp->create();
					}
					
					return array(
							"success" => $success,
							"file" => $new_file,
							"directory" => $export_dir
					);
		}
}
?>