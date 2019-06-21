<?php
namespace Maxcore\Io;


class File {

    private $exists;
    /*
     * File İnfo
     */
    private $file;
    private $file_name;
    private $file_size;
    private $file_base_name;
    private $file_dir_name;
    private $file_ext;
    private $file_info;
    private $file_path;
    private $file_full_path;
    private $file_url;

    /*
     * Kontrol
     */
    private $controls;
    private $errors;

    public function __construct($file = NULL) {

        $this->errors = array();
        $this->controls = array();


        if ($file != NULL) {

            $this->init($file);
        }
    }

    public function init($fileName) {

        $file = PUBLIC_DIR . "/" . $fileName;

        if (file_exists($file)) {

            $this->file_info = pathinfo($file);

            $this->file_dir_name = $this->file_info['dirname'];

            $this->file_base_name = $this->file_info['basename'];

            $this->file_ext = $this->file_info['extension'];

            $this->file_name = $this->file_info['filename'];

            $this->file_size = filesize($file);

            $this->file_info['size'] = $this->file_size;

            $this->exists = true;
            
            $this->file = $file;
            
            $this->file_full_path=$this->file_dir_name."/".$this->file_name.".".$this->file_ext;
            
            $this->file_path = str_replace(PUBLIC_DIR , "", $this->file_full_path);
            
            $this->file_url = PUBLIC_DIR."/".$this->file_path;

            $this->file_info["file_url"]=$this->file_url;
            
            $this->file_info["file_path"]=$this->file_path;
            
            $this->file_info["full_path"]=$this->file_full_path;
            
        } else {

            $this->exists = false;
        }

        return $this;
    }

    public function get_name() {
        return $this->file_name;
    }

   public function get_base_name() {
        return $this->file_base_name;
    }
    
    public function get_size() {
        return $this->file_size;
    }

    public function get_ext() {
        return $this->file_ext;
    }

    public function get_errors() {

        return $this->errors;
    }

    public function get_info() {
        return $this->file_info;
    }

    public function create($file, $data = NULL) {
        
    }

    public function control($type = []) {
        
    }

    /*
     * 
     *   public function upload(
     *          $_FILES["images"],
     *          upload_path,
     *          $control=[
     *          'ex'=>['jpg','png'],
     *          'max-size'=>241242
     *          ],
     *          "dosya yeni adı uzantı hariç"
     * 
     * ) {
     * 
     * 
     */

    public function upload($file, $upload_path = "", $control = [], $rename = NULL) {

        $file_name = $file['name'];

        $file_size = $file['size'];

        $file_tmp = $file['tmp_name'];

        $file_type = $file['type'];

        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);


        if (!is_dir(PUBLIC_DIR . "/" . $upload_path)) {

            mkdir(PUBLIC_DIR . "/" . $upload_path, 0777, true);
        }

        if (is_dir(PUBLIC_DIR . "/" . $upload_path)) {

            if (isset($control['ex'])) {

                if (in_array($file_ext, $control['ex']) === false) {

                    $this->errors[] = "Dosya Uzantısı İzin Verilen Uzantılardan Değil";
                }
            }

            if (isset($control['max-size'])) {
                if ($file_size > $control['max-size']) {

                    $this->errors[] = "Dosya Boyutu {$control['max-size']} sınırını aşıyor!";
                }
            }

            if (empty($this->errors) == true) {

                if ($rename != NULL) {

                    $file_name = $rename . "." . $file_ext;
                }

                move_uploaded_file($file_tmp, PUBLIC_DIR . "/" . $upload_path . $file_name);

                $this->init($upload_path . $file_name);

                return true;
            } else {

                return false;
            }
        } else {
            $this->errors[] = "Yükleme Klasörü Hatası";
            return false;
        }
    }

    public function rename($new_name) {
        
    }

    public function remove() {
        
    }

    public function copy($copy_name = NULL) {
        
    }

}
