<?php

namespace Drupal\importdata\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Routing;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\Response;
use \Drupal\node\Entity\Node;
/**
 * Provides the form for adding countries.
 */
class ImportexcelForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = array(
      '#attributes' => array('enctype' => 'multipart/form-data'),
    );

    $form['file_upload_details'] = array(
      '#markup' => t('<b>The File</b>'),
    );

    $validators = array(
      'file_validate_extensions' => array('csv'),
    );
    $form['excel_file'] = array(
      '#type' => 'managed_file',
      '#name' => 'excel_file',
      '#title' => t('File *'),
      '#size' => 20,
      '#description' => t('Excel format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://content/excel_files/',
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );

    return $form;

  }


  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('excel_file') == NULL) {
      $form_state->setErrorByName('excel_file', $this->t('upload proper File.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $file = \Drupal::entityTypeManager()->getStorage('file')->load($form_state->getValue('excel_file')[0]); // Just FYI. The file id will be stored as an array

	 $full_path = $file->get('uri')->value;
	 $file_name = basename($full_path);

		try{
		$inputFileName = \Drupal::service('file_system')->realpath('public://content/excel_files/'.$file_name);

		$spreadsheet = IOFactory::load($inputFileName);

		$sheetData = $spreadsheet->getActiveSheet();

		$rows = array();
		foreach ($sheetData->getRowIterator() as $row) {
			//echo "<pre>";print_r($row);exit;
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(FALSE);
			$cells = [];
			foreach ($cellIterator as $cell) {
				$cells[] = $cell->getValue();


			}
           $rows[] = $cells;

		}
		//echo "<pre>";print_r($rows);exit;
		//====remove first item since it is the header row
		array_shift($rows);
        // echo "<pre>";
        // print_r($rows); die();

		foreach($rows as $row) {
			$values = \Drupal::entityQuery('node')->condition('title', $row[0])->execute();
			$node_not_exists = empty($values);
			if($node_not_exists) {
				/*if node does not exist create new node*/
				$node = \Drupal::entityTypeManager()->getStorage('node')->create([
				  'type'                 => 'news', //===here news is the content type mechine name
				  'title'                => $row[0],
				  // 'field_store_id'       => $row[1],
				  // 'field_address1'       => $row[2],
				  // 'field_address2'       => $row[3],
				  // 'field_city'           => $row[4],
                  'field_address' =>  array(
                  	"country_code" => "US",
                    "address_line1" => "123 Fake Street",
                    "locality" => "Beverly Hills",
                    "administrative_area" => "CA",
                    "postal_code" => "90210"
                ),
				'field_phone'          => $row[8],
				'field_latitude'       => $row[9],
				'field_longitude'      => $row[10]
				]);
				$node->save();
			}
		}

		\Drupal::messenger()->addMessage('imported successfully');


		} catch (Exception $e) {
			\Drupal::logger('type')->error($e->getMessage());
        }
  }

}
