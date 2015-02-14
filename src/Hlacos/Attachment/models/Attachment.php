<?php

namespace Hlacos\Attachment;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Config;

class Attachment extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'attachments';

    /**
     * Image in these sizes will created.
     *
     * @var array $sizes
     */
    protected $sizes;

    /**
     * The temporary path of the upladed file.
     *
     * @var string
     */
    public $path;

    /**
     * Defines polymorphic relations to any other Model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function attachable() {
        return $this->morphTo();
    }

    /**
     * Saves the file.
     *
     * When saves a file, it persists the model and move the uploaded file into the place.
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = array()) {
        parent::save($options);

        if ($this->moveFile($this->path)) {
            if (count($this->sizes)) {
                foreach($this->sizes as $size) {
                    $this->copySize($size);
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Sets model instance attributes.
     *
     * @param string $path
     *
     * @return void
     */
    public function addFile($path) {
        $this->path = $path;
        $this->filename = pathinfo($path, PATHINFO_FILENAME);
        $this->extension = pathinfo($path, PATHINFO_EXTENSION);
        $this->size = filesize($path);
        $this->file_type = mime_content_type($path);
    }

    /**
     * Moves uploaded file to place.
     *
     * @param string $path
     *
     * @return bool
     */
    public function moveFile($path) {
        if (!file_exists(public_path().'/'.$this->basePath())) {
            mkdir(public_path().'/'.$this->basePath(), 0777, true);
        }

        return rename($path, $this->publicPath());
    }

    /**
     * Gets the public path of the file.
     *
     * @return string
     */
    public function publicPath($size = null) {
        $publicPath = public_path().$this->publicFilename();
        if ($size) {
            return str_replace('.'.$this->extension, '_'.$size.'x'.$size.'.'.$this->extension, $publicPath);
        } else {
            return $publicPath;
        }
    }

    /**
     * Gets the public url of the file.
     *
     * @param int $size
     *
     * @return string
     */
    public function publicUrl($size = null) {
        $publicUrl = asset($this->publicFilename());

        if ($size) {
            return str_replace('.'.$this->extension, '_'.$size.'x'.$size.'.'.$this->extension, $publicUrl);
        } else {
            return $publicUrl;
        }
    }

    private function basePath() {
        //TODO: könyvtárszerkezetet módosítani, esetleg uuid-s megoldással.
        return '/'.Config::get('attachment::attachment.folder').'/'
            .self::sanitize($this->get_real_class(), true, true)
            .'/'.$this->id.'/';
    }

    private function publicFilename() {
        return $this->basePath().$this->baseFilename();
    }

    private function baseFilename() {
        return $this->filename.'.'.$this->extension;
    }

    private function copySize($size) {
        list($width, $height) = getimagesize($this->publicPath());

        if ($width >= $height) {
            $newWidth = $size;
            $newHeight = round(($newWidth / $width) * $height);
        } else {
            $newHeight = $size;
            $newWidth = round(($newHeight / $height) * $width);
        }

        // Load
        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        if ($this->extension == 'jpg') {
            $source = imagecreatefromjpeg($this->publicPath());
            imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagejpeg($thumb, $this->publicPath($size));
        } elseif ($this->extension == 'jpeg') {
            $source = imagecreatefromjpeg($this->publicPath());
            imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagejpeg($thumb, $this->publicPath($size));
        } elseif ($this->extension == 'png') {
            $source = imagecreatefrompng($this->publicPath());
            imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagepng($thumb, $this->publicPath($size));
        }
    }

    public static function sanitize($string, $forceLowercase = false, $alpha = false) {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($alpha) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
        return ($forceLowercase) ?
            (function_exists('mb_strtolower')) ?
                mb_strtolower($clean, 'UTF-8') :
                strtolower($clean) :
            $clean;
    }

    private function get_real_class() {
        $classname = get_class($this);

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
    }
}
