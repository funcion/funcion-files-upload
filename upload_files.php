<?php

/**
 * TEST PROJECT 
 * 
 * Funcion_upload_file
 * 
 * It is ver  simple class written in PHP code to upload, move and delete files from the server.
 *
 * This class allows you to upload, move and delete files uploaded from your website to the server
 * in a way, fast, efficient and safe, allows to establish certain basic parameters that
 * are used very often to check the files that the user uploads to the server, you can
 * establish and configure the weight, extensions, number of files allowed, maintain the
 * same name of the file, about writing or not files, validate images by MIME TYPE or
 * only for the extension of the file, works with absolute URL of the server, why
 * You will not have to deal with directories, I plan to continue working on it and improve it.
 *
 * To use the class I recommend using PHP 5.5>, if they can bring improvements for development
 * It would be well received, make a fork of the project and I will be very aware of the changes and improvements
 * to value them and do the merge if necessary.
 *
 * This class was developed by an apprentice programmer for personal use, I have decided to share it
 * for free so that other users that need in their project upload files to the server
 * in a quickly configurable way, without so many complications you can use it, also if you want
 * improve it and help integrate new features, such as creating Thumbnails, manipulating images etc ...
 * You can do it and help me improve it, but the use of this class is without any guarantee
 * It is the responsibility of the user to download or copy it, I would appreciate to keep
 * the credits and if you have any questions or suggestions, you can write me and I will be happy to help you.
 *
 * MIT License
 * 
 * Copyright (c) [2018] [Luis Figuera]
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the right0
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @package    Funcion upload file
 * @author     Luis Figuera <sifuncion@gmail.com>
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @since	Version 1.0.0
 * 
 */

class Funcion_upload_file {

	/**
	 * Default var - Upload Files
	 * @var [string]  $size               [File size by default]
	 * @var [string]  $extentions         [Permitted extensions]
	 * @var [string]  $max_files_uploaded [Total max files to be sent]
	 * @var [boolean] $original_name      [Keep the original name of the file]
	 * @var [string]  $path               [Absolute server URL and file folder]
	 * @var [boolean] $mime_type_validate [Validate all files with the MIME TYPE]
	 */
	public $size                = '2';
	public $extentions          = 'jpg,png,jpeg,gif';
	public $max_files_uploaded  = '5';
	public $original_name       = true;
	public $overwrite       	= false;
	public $mime_type_validate  = true;
	public $path                = 'files';
	public $path_temp 			= "temp";


	/**
	 * [config Join and establish configurations established by the user]
	 * @param  array  $config_user [Array of the configurations established by the user]
	 * @return [array]
	 */
	public function config($config_user = array())
	{
		/**
		 * Default config
		 */
		$config_default = array(
			'size'                	=> $this->size,
			'extentions'          	=> $this->extentions,
			'max_files_uploaded'  	=> $this->max_files_uploaded,
			'original_name'       	=> $this->original_name,
			'overwrite'       	 	=> $this->overwrite,
			'mime_type_validate'  	=> $this->mime_type_validate,
			'path'           	  	=> $this->path
		);
		// Check if the user sent configuration information
		if(count($config_user) > 0) {
			// Join arrays received by the user with the ones established by default
			$config_default = array_merge($config_default, $config_user);
			return $config_default;
		}
		return $config_default;
	}


	/*** 

	 * MASTER FUNCTION
	 * 
	 * [upload Upload file/s ]
	 * @param string $name_field [Name of file field]
	 * @param array  $config_user [Configurations established outside the class by the user]
	 * @return [Array] [Array with file uploads]
	 * /
	 */
	public function upload($name_field = 'image', $config_user = array())
	{
		/**
		 * Default config
		 */
		$path_server_root = realpath(__DIR__) . DIRECTORY_SEPARATOR;
		$config_merge = self::config( $config_user );
		$config = array(
			'size'               	=> self::get_bytes( $config_merge['size'] ),
			'extentions'         	=> self::get_mime_type_config( $config_merge['extentions'] ),
			'max_files_uploaded' 	=> $config_merge['max_files_uploaded'],
			'original_name'      	=> $config_merge['original_name'],
			'overwrite'				=> $config_merge['overwrite'],
			'mime_type_validate' 	=> $config_merge['mime_type_validate'],
			'path'          	 	=> $path_server_root . rtrim( ltrim($config_merge['path'], '/') , '/') . '/'
		);

		/**
		 * Default var
		 */
		$status = array( 'status' => true, 'message' => '');
		
		/**
		 * Check if a single file was sent
		 */
		if( !is_array($_FILES[$name_field]['name']) && !empty($_FILES[$name_field]['name']) ) {
			// Check if not is empty
			$upload_file = self::upload_file($name_field, $config);
			return $upload_file;
		} elseif( is_array($_FILES[$name_field]['name']) && !empty($_FILES[$name_field]['name'][0]) ) {
			// Upload files
			$files = self::upload_files($name_field, $config);
			return $files;
		} else {
			$status = array(
				'status' => false,
				'message' => "<p class='message'>The field <strong>{$name_field}</strong> it cant be empty.</p>"
			);
			return $status;
		}
	}


	/**
	 * [upload_file Upload a single file]
	 * @param  [string] $name_field [File field name]
	 * @param  [array] 	$config     [Configurations of the class and those established outside the class by the user]
	 * @return [Array OR Boolean]
	 */
	public function upload_file($name_field, $config)
	{
		/**
		 * Default config
		 * @var [array]  $config_users_setting [Configurations set by user without of class and unprocessed user]
		 * @var [string] $custom_name_pattern  [Pattern for the name of the images]
		 * @var [array]  $status               [Array for file uploads]
		 * @var [string] $file_name            [Sanitized file name]
		 * @var [string] $file_name_temp       [Temporary file name]
		 * @var [string] $file_size            [File size]
		 * @var [string] $file_type            [File type]
		 * @var [array]  $extentions_user      [User Extensions Array]
		 * @var [array]  $file_name_pieces     [Array with name and file extension]
		 */
		$config_users_setting 	= self::config();
		$custom_name_pattern 	= date('Y-m-d-h-i-s_') . uniqid();
		$status         		= array( 'status' => true, 'message' => '');
		$file_name      		= self::sanitize_text( $_FILES[$name_field]['name'] );
		$file_name_temp 		= $_FILES[$name_field]['tmp_name'];
		$file_size      		= $_FILES[$name_field]['size'];
		$file_type      		= $_FILES[$name_field]['type'];
		$extentions_user 		= explode(",", $config_users_setting['extentions']);
		$file_name_pieces	 	= self::get_filename_pieces( $file_name );

		// Check file size
		if($file_size > $config['size']) {
			$status = array(
				'status' => false,
				'message' => "<p class='message'>The file exceeds the allowed size.</p>"
			);
			return $status;
		}

		// Check if the user wants to validate the MIME TYPE or extension of the file
		if($config['mime_type_validate'] === true) {
			// Check MIME TYPE
			if(!in_array($file_type, $config['extentions'])) {
				$status = array(
					'status' => false,
					'message' => "<p class='message'>The file is not allowed, valid extensions ({$config_users_setting['extentions']}).</p>"
				);
				return $status;
			}
		} else {
			if(!in_array($file_name_pieces['extention'], $extentions_user)) {
				$status = array(
					'status' => false,
					'message' => "<p class='message'>The file is not allowed, valid extensions ({$config_users_setting['extentions']}).</p>"
				);
				return $status;
			}
		}

		// Check if directory does not exist
		if(!is_dir($config['path'])) {
			// Crea directorio
			if(!mkdir($config['path'], 0755, true)) {
				$status = array(
					'status' => false,
					'message' => "<p class='message'>Directory not created, try again.</p>"
				);
				return $status;
			}
		}

		// Check if the user wants to keep the file name
		if($config['original_name'] === true) {
			// Check if user wants to overwrite the existing file
			if($config['overwrite'] === false) {
				// Check if file exists
				if( self::check_file_isset($file_name, $config['path']) ) {
					$file_name = $file_name_pieces['name'].'-'.uniqid().'.'.$file_name_pieces['extention'];
				}
			}
		} else {
			// If it does not exist, we use custom name
			$file_name = $custom_name_pattern.'.'.$file_name_pieces['extention'];
		}

		// Upload file
		if($qwe = self::do_upload($file_name_temp, $config['path'], $file_name)) {
			$status = array(
				'status' => true,
				'message' => "<p class='message'>The file has been uploaded correctly.</p>"
			);
			return $status;
		}
	}


	/**
	 * [upload_files Upload multiple files]
	 * @param  [string] $name_field [File field name]
	 * @param  [array] 	$config     [Configurations of the class and those established outside the class by the user]
	 * @return [Array OR Boolean]
	 */
	public function upload_files($name_field, $config)
	{
		/**
		 * Default config
		 * @var [array]  $config_users_setting [Configurations set by the unprocessed user]
		 * @var [string] $custom_name_pattern  [Patron for the name of the images]
		 * @var [array]  $status               [Array for file uploads]
		 * @var [string] $file_name            [Sanitized file name]
		 * @var [string] $file_name_temp       [Temporary file name]
		 * @var [string] $file_size            [File size]
		 * @var [string] $file_type            [File type]
		 * @var [array]  $extentions_user      [User Extensions Array]
		 * @var [array]  $file_name_pieces     [Array with name and file extension]
		 */
		$config_users_setting 	= self::config();

		/**
		 * Sort array of multiple files
		 */
		$files = self::order_array_files($_FILES[$name_field]);

		/**
		 * Validate files
		 */
		foreach ($files as $key => $value) {
			$custom_name_pattern 	= date('Y-m-d-h-i-s_') . uniqid();
			$status         		= array( 'status' => true, 'message' => '');
			$file_name      		= self::sanitize_text( $files[$key]['name'] );
			$file_name_original		= $files[$key]['name'];
			$file_name_temp 		= $files[$key]['tmp_name'];
			$file_size      		= $files[$key]['size'];
			$file_type      		= $files[$key]['type'];
			$extentions_user 		= explode(",", $config_users_setting['extentions']);
			$file_name_pieces	 	= self::get_filename_pieces( $file_name );

			// Valid number of files to upload
			if(count($files) > $config['max_files_uploaded']) {
				$status = array(
					'status' => false,
					'message' => "<p class='message'>Number of invalid files.</p>"
				);
				return $status;
			}

			// Check file size
			if($file_size > $config['size']) {
				$status = array(
					'status' => false,
					'message' => "<p class='message'>The file exceeds the allowed size.</p>"
				);
				return $status;
			}

			// Check if the user wants to validate the MIME TYPE or extension of the file
			if($config['mime_type_validate'] === true) {
				// Check MIME TYPE
				if(!in_array($file_type, $config['extentions'])) {
					$status = array(
						'status' => false,
						'message' => "<p class='message'>The file is not allowed, valid extensions ({$config_users_setting['extentions']}).</p>"
					);
					return $status;
				}
			} else {
				if(!in_array($file_name_pieces['extention'], $extentions_user)) {
					$status = array(
						'status' => false,
						'message' => "<p class='message'>The file is not allowed, valid extensions ({$config_users_setting['extentions']}).</p>"
					);
					return $status;
				}
			}

			// Check if directory does not exist
			if($key === 0) {
				if(!is_dir($config['path'])) {
					// Crea directorio
					if(!mkdir($config['path'], 0755, true)) {
						$status = array(
							'status' => false,
							'message' => "<p class='message'>Directory not created, try again.</p>"
						);
						return $status;
					}
				}
			}

			// Check if the user wants to keep the file name
			if($config['original_name'] === true) {
				// Check if user wants to overwrite the existing file
				if($config['overwrite'] === false) {
					// Check if file exists
					if( self::check_file_isset($file_name, $config['path']) ) {
						$file_name = $file_name_pieces['name'].'-'.uniqid().'.'.$file_name_pieces['extention'];
					}
				}				
			} else {
				// If it does not exist, we use custom name
				$file_name = $custom_name_pattern.'.'.$file_name_pieces['extention'];
			}

			// Upload files
			if( !self::do_upload($file_name_temp, $config['path'], $file_name) ) {
				$status = array(
					'status' => false,
					'message' => "<p class='message'>The file could not be uploaded{<strong>$file_name_original</strong>}.</p>"
				);
				return $status;
			}
		}

		/**
		 * [$status Show status]
		 * @var array
		 */
		$status = array(
			'status' => false,
			'message' => "<p class='message'>All the files were uploaded.</p>"
		);
		return $status;
	}


	/**
	 * [get_bytes Get total bytes]
	 * @return [string] [Total bites]
	 */
	public function get_bytes($size)
	{
		$size_progress = preg_replace('/[^0-9]/', '', $size);
		$size          = $size_progress * 1024 * 1024;
		return $size;
	}


	/**
	 * [get_mime_type_file Get the MIME TYPE from the file]
	 * @param  [string] $name_field [File field name]
	 * @return [string] 			[MIME TYPE file]
	 */
	public function get_mime_type_file($name_field)
	{
		$finfo = new finfo(FILEINFO_MIME_TYPE);
	    $file_contents = file_get_contents($_FILES[$name_field]["tmp_name"]);
	    $mime_type = $finfo->buffer($file_contents);
	    $mime_type = strtolower($mime_type);
	    return $mime_type;
	}


	/**
	 * [sanitize_text Remove all special characters from a string]
	 * @param  [string] $str       	[Character string]
	 * @param  string $delimiter 	[Delimiter for spaces]
	 * @return [Boolean OR String]
	 */
	public function sanitize_text($string, $delimiter = '-')
	{
		$string = str_replace(' ', '-', $string);
   		$string = preg_replace('/[^A-Za-z0-9\-\.]/', '', $string);
   		$string = strtolower($string);
   		return $string;
	}


	/**
	 * [get_filename_pieces Get extension of a file]
	 * @param  [string] $file_name [Filename]
	 * @return [array] [Array with the name and extension of the file]
	 */
	function get_filename_pieces($file_name) {
		// Convert string to array
		$name_piece = explode(".", $file_name);
		// Get the extension
		$extention = array_pop($name_piece);
		// Convert the Array of the name without the extension into a string
		$name_string = implode('-', $name_piece);
		// Image name array
		$info = array(
			'name' => strtolower($name_string),
			'extention' => strtolower($extention)
		);
		return $info;
	}


	/**
	 * [check_file_isset Check if file exists]
	 * @param  [string] $file_name [File field name]
	 * @param  [string] $path      [Absolute URL of the file]
	 * @return [Boolean]
	 */
	public function check_file_isset($file_name, $path)
	{
		if(file_exists($path . $file_name)) {
			return true;
		}
		return false;
	}


	/**
	 * [do_upload Upload the file]
	 * @param  [string] $file_name [File field name]
	 * @param  [string] $path      [Absolute URL of the file]
	 * @return [Boolean]
	 */
	public function do_upload($file_name_temp, $path, $file_name)
	{
		if(move_uploaded_file($file_name_temp, $path . $file_name)) {
			return true;
		}
		return false;
	}


	/**
	 * [order_array_files Sort array of multiple files]
	 * @param  [string] &$file_name [File field name]
	 * @return [array] 
	 */
	function order_array_files(&$file_name)
	{
	    $file_array = array();
	    $file_count = count($file_name['name']);
	    $file_keys = array_keys($file_name);
	    for ($i=0; $i<$file_count; $i++) {
	        foreach ($file_keys as $key) {
	            $file_array[$i][$key] = $file_name[$key][$i];
	        }
	    }
	    return $file_array;
	}

	/**
	 *
	 * MASTER FUNCTION
	 * 
	 * [move Move files from one folder to another]
	 * @param  [type] $file_name  	[Filename]
	 * @param  [string] $path_temp  [Temporary or current file folder]
	 * @param  [string] $path_final [Final file folder]
	 * @return [array]
	 */
	public function move($file_name, $path_temp, $path_final)
	{
		/**
		 * Default var
		 */
		$status     = array( 'status' => true, 'message' => '');
		$path_temp = rtrim( ltrim($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $path_temp, '/') , '/') . '/';
		$path_final = rtrim( ltrim($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $path_final, '/') , '/') . '/';

		/**
		 * Check if temporary directory exists
		 */
		if(!is_dir($path_temp)) {
			$status = array( 
				'status' => false, 
				'message' => 'The temporary directory does not exist'
			);
			return $status;
		}

		/**
		 * Check if final directory exists
		 */
		if(!is_dir($path_final)) {
			$status = array( 
				'status' => false, 
				'message' => 'The final directory does not exist'
			);
			return $status;
		}
		
		/**
		 * Check if it is a file or several files
		 */
		if( !is_array($file_name) && !empty($file_name) ) {
			$move_file = self::move_file($file_name, $path_temp, $path_final);
			return $move_file;
		} else {
			$move_files = self::move_files($file_name, $path_temp, $path_final);
			return $move_files;
		}
	}


	/**
	 * [move_file Move a single file]
	 * @param  [type] $file_name  [Filename]
	 * @param  [type] $path_temp  [Absolute path of the temporary folder]
	 * @param  [type] $path_final [Absolute path of the final folder]
	 * @return [Boolean]
	 */
	public function move_file($file_name, $path_temp, $path_final)
	{
		/**
		 * Default var
		 */
		$status     = array( 'status' => true, 'message' => '');
		$path_temp_file = $path_temp . $file_name;
		$path_final_file = $path_final . $file_name;
		// Check if file exists
		if(self::check_file_isset($file_name, $path_temp)) {
			if(rename($path_temp_file, $path_final_file)) {
				$status = array( 
					'status' => true, 
					'message' => 'File moved'
				);
				return $status;
	        } else {
	        	// Copy file
	        	if(copy($path_temp_file, $path_final_file)) {
	        		// Delete file
					if(unlink($path_temp_file)) {
						$status = array( 
							'status' => true, 
							'message' => 'File has been copied and removed from the temporary folder'
						);
						return $status;
					} else {
						$status = array( 
							'status' => false, 
							'message' => 'The file could not be deleted'
						);
						return $status;
					}
				} else {
					$status = array( 
						'status' => false, 
						'message' => 'The file could not be copied'
					);
					return $status;
				}
	        }
		} else {
			$status = array( 
				'status' => false, 
				'message' => 'The file is not in this folder'
			);
			return $status;
		}
	}


	/**
	 * [move_files Move multiple files]
	 * @param [string OR array] $file_names [Filenames]
	 * @param [string] 			$path_temp	[Absolute path of the temporary folder]
	 * @param [string] 			$path_final	[Absolute path of the final folder]
	 * @return [Boolean]
	 */
	public function move_files($file_names, $path_temp, $path_final)
	{
		/**
		 * Default var
		 */
		$status     = array( 'status' => true, 'message' => '');
		foreach ($file_names as $key => $value) {
			$path_temp_file = $path_temp . $value;
			$path_final_file = $path_final . $value;
			// Check if file exists
			if(self::check_file_isset( $value, $path_temp)) {
				// Moved file
				if(rename($path_temp_file, $path_final_file)) {
					continue;
		        } else {
		        	// Copy file
		        	if(copy($path_temp_file, $path_final_file)) {
		        		// Delete file
						if(unlink($path_temp_file)) {
							continue;
						} else {
							$status = array( 
								'status' => false, 
								'message' => 'The file could not be deleted'
							);
							return $status;
						}
					} else {
						$status = array( 
							'status' => false, 
							'message' => 'The file could not be copied'
						);
						return $status;
					}
		        }
			} else {
				$status = array( 
					'status' => false, 
					'message' => 'The file is not in this folder'
				);
				return $status;
			}	
		}

		// Show meesages
		$status = array( 
			'status' => true, 
			'message' => 'All files has been copied and removed from the temporary folder'
		);
		return $status;
	}


	/**
	 * 
	 * MASTER FUNCTION
	 * 
	 * [delete Delete files from the server]
	 * @param  [type] 	$file_name 	[Filename]
	 * @param  [string] $path 		[Directory of the file]
	 * @return [array]
	 */
	public function delete($file_name, $path)
	{
		/**
		 * Default var
		 */
		$status	= array( 'status' => true, 'message' => '');
		$path 	= rtrim( ltrim($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $path, '/') , '/') . '/';

		/**
		 * Check if directory exists
		 */
		if(!is_dir($path)) {
			$status = array( 
				'status' => false, 
				'message' => 'The directory does not exist'
			);
			return $status;
		}

		/**
		 * Check if it is empty
		 */
		if(empty($file_name)){
			$status = array( 
				'status' => false, 
				'message' => 'The file can not be empty'
			);
			return $status;
		}
		
		/**
		 * Check if it is a file or several files
		 */
		if( !is_array($file_name) ) {
			$delete_file = self::delete_file($file_name, $path);
			return $delete_file;
		} else {
			$delete_files = self::delete_files($file_name, $path);
			return $delete_files;
		}
	}


	/**
	 * [delete_file Delete a single file]
	 * @param  [type] $file_name	[Filename]
	 * @param  [type] $path  		[Directory of the file]
	 * @return [Boolean]
	 */
	public function delete_file($file_name, $path)
	{
		/**
		 * Default var
		 */
		$status	= array( 'status' => true, 'message' => '');
		$path_file = $path . $file_name;

		// Check if file exists
		if(self::check_file_isset($file_name, $path)) {
			if(unlink($path_file)) {
				$status = array( 
					'status' => true, 
					'message' => 'The file has been delete'
				);
				return $status;
			} else {
				$status = array( 
					'status' => false, 
					'message' => 'The file could not be deleted'
				);
				return $status;
			}
		} else {
			$status = array( 
				'status' => false, 
				'message' => 'The file is not in this folder'
			);
			return $status;
		}
	}

	/**
	 * [delete_files Delete files]
	 * @param  [type] $file_name	[Filename]
	 * @param  [type] $path  		[Directory of the file]
	 * @return [Boolean]
	 */
	public function delete_files($file_names, $path)
	{
		/**
		 * Default var
		 */
		$status	= array( 'status' => true, 'message' => '');

		foreach ($file_names as $key => $value) {
			/**
			 * Default var
			 */
			$path_file = $path . $value;

			// Check if file exists
			if(self::check_file_isset($value, $path)) {
				if(unlink($path_file)) {
					continue;
				} else {
					$status = array( 
						'status' => false, 
						'message' => 'The file could not be deleted'
					);
					return $status;
				}
			} else {
				$status = array( 
					'status' => false, 
					'message' => 'The file is not in this folder'
				);
				return $status;
			}	
		}

		// Show meesages
		$status = array( 
			'status' => true, 
			'message' => 'Files removed'
		);
		return $status;
	}

	/**
	 * [get_mime_type_config Get all the MIME TYPE of the extensions specified by the user]
	 * @param  [string] $extention 	[Extensions specified by the user]
	 * @link https://www.iana.org/assignments/media-types/media-types.xhtml Complete list MIME TYPE
	 * @link https://developer.mozilla.org/es/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Lista_completa_de_tipos_MIME List MIME TYPE
	 * @return [Boolean or Array]
	 */
	public function get_mime_type_config($extentions_user)
	{
		/**
		 * Default var
		 */
		$extention_progress = array();	
		$extention = array();	

		// Convert the extensions specified by the user to an array
		$extention_progress = explode(',', $extentions_user);
		// Eliminates duplicate extensions
		$extention_progress = array_unique($extention_progress);
		// Convert all extensions to lowercase
		$extention_progress = array_map('strtolower', $extention_progress);
		// Add the values of the array to the keys
		foreach ($extention_progress as $key => $value) {
			$extention[$value] = $value;
		}

		// Mimes default
		$mime_types = array(
			"docx"    => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
			"docm"    => "application/vnd.ms-word.document.macroEnabled.12",
			"dotx"    => "application/vnd.openxmlformats-officedocument.wordprocessingml.template",
			"dotm"    => "application/vnd.ms-word.template.macroEnabled.12",
			"xlsx"    => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
			"xlsm"    => "application/vnd.ms-excel.sheet.macroEnabled.12",
			"xltx"    => "application/vnd.openxmlformats-officedocument.spreadsheetml.template",
			"xltm"    => "application/vnd.ms-excel.template.macroEnabled.12",
			"xlsb"    => "application/vnd.ms-excel.sheet.binary.macroEnabled.12",
			"xlam"    => "application/vnd.ms-excel.addin.macroEnabled.12",
			"pptx"    => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
			"pptm"    => "application/vnd.ms-powerpoint.presentation.macroEnabled.12",
			"ppsx"    => "application/vnd.openxmlformats-officedocument.presentationml.slideshow",
			"ppsm"    => "application/vnd.ms-powerpoint.slideshow.macroEnabled.12",
			"potx"    => "application/vnd.openxmlformats-officedocument.presentationml.template",
			"potm"    => "application/vnd.ms-powerpoint.template.macroEnabled.12",
			"ppam"    => "application/vnd.ms-powerpoint.addin.macroEnabled.12",
			"sldx"    => "application/vnd.openxmlformats-officedocument.presentationml.slide",
			"sldm"    => "application/vnd.ms-powerpoint.slide.macroEnabled.12",
			"one"     => "application/onenote",
			"onetoc2" => "application/onenote",
			"onetmp"  => "application/onenote",
			"onepkg"  => "application/onenote",
			"thmx"    => "application/vnd.ms-officetheme",
			"abw"     => "application/x-abiword",
			"arc"     => "application/octet-stream",
			"azw"     => "application/vnd.amazon.ebook",
			"bin"     => "application/octet-stream",
			"bz"      => "application/x-bzip",
			"bz2"     => "application/x-bzip2",
			"csh"     => "application/x-csh",
			"doc"     => "application/msword",
			"epub"    => "application/epub+zip",
			"jar"     => "application/java-archive",
			"js"      => "application/javascript",
			"json"    => "application/json",
			"mpkg"    => "application/vnd.apple.installer+xml",
			"odp"     => "application/vnd.oasis.opendocument.presentation",
			"ods"     => "application/vnd.oasis.opendocument.spreadsheet",
			"odt"     => "application/vnd.oasis.opendocument.text",
			"ogx"     => "application/ogg",
			"pdf"     => "application/pdf",
			"ppt"     => "application/vnd.ms-powerpoint",
			"rar"     => "application/x-rar-compressed",
			"rtf"     => "application/rtf",
			"sh"      => "application/x-sh",
			"swf"     => "application/x-shockwave-flash",
			"tar"     => "application/x-tar",
			"vsd"     => "application/vnd.visio",
			"xhtml"   => "application/xhtml+xml",
			"xls"     => "application/vnd.ms-excel",
			"xml"     => "application/xml",
			"xul"     => "application/vnd.mozilla.xul+xml",
			"zip"     => "application/zip",
			"7z"      => "application/x-7z-compressed",
			"ttf"     => "font/ttf",
			"woff"    => "font/woff",
			"woff2"   => "font/woff2",
			"css"     => "text/css",
			"csv"     => "text/csv",
			"htm"     => "text/html",
			"html"    => "text/html",
			"ics"     => "text/calendar",
			"gif"     => "image/gif",
			"ico"     => "image/x-icon",
			"jpg"     => "image/jpeg",
			"jpeg"    => "image/jpeg",
			"png"    => "image/png",
			"bmp"    => "image/bmp",
			"svg"     => "image/svg+xml",
			"tif"     => "image/tiff",
			"tiff"    => "image/tiff",
			"webp"    => "image/webp",
			"aac"     => "audio/aac",
			"mid"     => "audio/midi",
			"midi"    => "audio/midi",
			"oga"     => "audio/ogg",
			"wav"     => "audio/x-wav",
			"weba"    => "audio/webm",
			"3gp"     => "audio/3gpp",
			"3g2"     => "audio/3gpp2",
			"avi"     => "video/x-msvideo",
			"mpeg"    => "video/mpeg",
			"ogv"     => "video/ogg",
			"webm"    => "video/webm",
			"3gp"     => "video/3gpp",
			"3g2"     => "video/3gpp2"
		);

		// Get all the MIME TYPE of the extensions specified by the user with their index in the array
		$extention = array_intersect_key($mime_types, $extention);
		// Get all the values of the array
		$extention = array_values($extention);
		// Convert all MIME TYPE values in the array to lowercase
		$extention = array_map('strtolower', $extention);
		// Validation
		if(is_array($extention) && count($extention) > 0) {
			return $extention;
		}
		return false;
	}
}


$upload = new Funcion_upload_file();

if(isset($_POST['send'])) {

$config = array(
	'size' => '2',
	'extentions' => 'jpg,png,jpeg',
	'max_files_uploaded' => '20',
	'original_name' => true,
	'overwrite' => false,
	'path' => 'files/',
	'mime_type_validate' => true
);
$upload_file = $upload->upload('file', $config);
echo "<pre>"; print_r($upload_file);

}

?>
<html>
	<body>
		<form action="" method="post" enctype="multipart/form-data">
			<input type="file" name="file[]" multiple>
			<input type="submit" name="send" value="Send">
		</form>
	</body>
</html>