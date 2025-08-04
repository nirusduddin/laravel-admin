<?php

namespace Encore\Admin\Form\Field;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Image extends File
{
    use ImageField;

    /**
     * {@inheritdoc}
     */
    protected $view = 'admin::form.file';

    /**
     *  Validation rules.
     *
     * @var string
     */
    protected $rules = 'image';

    /**
     * @param array|UploadedFile $image
     *
     * @return string
     */
    public function prepare($image)
    {
        if ($this->picker) {
            return parent::prepare($image);
        }
    
        if (request()->has(static::FILE_DELETE_FLAG)) {
            return $this->destroy();
        }
    
        // 🔒 ตรวจ MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $image->getRealPath());
        finfo_close($finfo);
    
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowedMime)) {
            throw new \Exception("Invalid MIME type: {$mime}");
        }
    
        // 🔒 ตรวจ extension
        $extension = strtolower($image->getClientOriginalExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            throw new \Exception("Invalid file extension: {$extension}");
        }
    
        $this->name = $this->getStoreName($image);
    
        $this->callInterventionMethods($image->getRealPath());
    
        $path = $this->uploadAndDeleteOriginal($image);
    
        $this->uploadAndDeleteOriginalThumbnail($image);
    
        return $path;
    }

    
    /**
     * force file type to image.
     *
     * @param $file
     *
     * @return array|bool|int[]|string[]
     */
    public function guessPreviewType($file)
    {
        $extra = parent::guessPreviewType($file);
        $extra['type'] = 'image';

        return $extra;
    }
}
