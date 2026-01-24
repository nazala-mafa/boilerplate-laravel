<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    public $table = 'file_uploads';

    protected $fillable = [
        'fileuploadable_type',
        'fileuploadable_id',
        'uploaderable_type',
        'uploaderable_id',
        'original_filename',
        'filepath',
        'is_used',
    ];

    protected $casts = [
        'is_used' => 'boolean'
    ];

    public function fileuploadable()
    {
        return $this->morphTo();
    }

    public function uploaderable()
    {
        return $this->morphTo();
    }

    public function scopeOfFileUrl($query, $fileUrl, $operator = '=')
    {
        $urlPath = str_replace(config('app.url') . '/storage/', "", $fileUrl);
        return $query->where('filepath', $operator, $urlPath);
    }

    public function getFileurlAttribute()
    {
        if (!$this->attributes['filepath']) {
            return null;
        }
        return config('app.url') . $this->attributes['filepath'];
    }

    public function isValidFileurl($fileUrl)
    {
        $urlPath = str_replace(config('app.url') . '/storage/', "", $fileUrl);

        if (str_starts_with($urlPath, '/private')) { // private file
            $fileUpload = $this->ofFileUrl($fileUrl)->first(['filepath']);
            
            if ($fileUpload === null) { // fileurl tidak ditemukan di db
                return false;
            }

            if (!file_exists(storage_path("app/$urlPath"))) { // file tidak ditemukan di storage
                return false;
            }

            return true;
        } else { // public file
            $fileUpload = $this->ofFileUrl($fileUrl)->first(['filepath']);
            
            if ($fileUpload === null) { // fileurl tidak ditemukan di db
                return false;
            }
            
            if (!file_exists(storage_path("app/public/$fileUpload->filepath"))) { // file tidak ditemukan di storage
                return false;
            }

            return true;
        }
    }

    public function updateIsUsed(Model $associatedModel, $fileUrl, $isUsed = true)
    {
        $existedAssociatedModel = $this
            ->whereMorphedTo('fileuploadable', $associatedModel)
            ->where('is_used', '=', 1)
            ->ofFileUrl($fileUrl, '!=');
        
        if ($existedAssociatedModel->exists()) {
            $existedAssociatedModel->update(['is_used' => false]);
        }

        $uploadedFile = $this->ofFileUrl($fileUrl)->first('id');

        if ($uploadedFile == null) {
            return;
        }

        $uploadedFile->fileuploadable()->associate($associatedModel);
        $uploadedFile->is_used = $isUsed;
        $uploadedFile->save();
    }
}
