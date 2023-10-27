<?php
namespace App\includes\library;

class Uploadsvalidation {
	
	public function check_name_length($object) {
		if (mb_strlen($object->file['original_filename']) > 5) {
			$object->set_error('File name is too long.');
		}
	}
  
	
}
?>