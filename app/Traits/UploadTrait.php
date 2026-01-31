<?php

namespace App\Traits;

use Laravolt\Avatar\Facade as Avatar;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;

trait UploadTrait
{

  public function uploadAllTyps($file, $directory, $width = null, $height = null, $hasType = false)
  {
    if (!File::isDirectory(storage_path('app/public/images/' . $directory))) {
      File::makeDirectory(storage_path('app/public/images/' . $directory), 0777, true, true);
    }

    $fileMimeType = $file->getClientmimeType();
    $imageCheck = explode('/', $fileMimeType);
    if ($imageCheck[0] == 'image') {
      $allowedImagesMimeTypes = $this->allowedImagesMimeTypes();
      if (!in_array($fileMimeType, $allowedImagesMimeTypes))
        return 'default.png';

      if ($hasType) {
        return [
          'name' => $this->uploadeImage($file, $directory, $width, $height),
          'type' => $imageCheck[0] == 'image' ? 'image' : 'file'
        ];
      }
      return $this->uploadeImage($file, $directory, $width, $height);
    }

    $allowedMimeTypes = ['application/pdf', 'application/msword', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/octet-stream'];
    if (!in_array($fileMimeType, $allowedMimeTypes)) {
      return 'default.png';
    }


    if ($hasType) {
      return [
        'name' => $this->uploadFile($file, $directory),
        'type' => $imageCheck[0] == 'image' ? 'image' : 'file'
      ];
    }

    return $this->uploadFile($file, $directory);
  }

  public function uploadFile($file, $directory)
  {
    if (is_file($file)) {
      $filename = time() . rand(1000000, 9999999) . '.' . $file->getClientOriginalExtension();
      $path = 'images/' . $directory;
      $file->storeAs($path, $filename, 'public');
      return $filename;
    }
  }

  public function uploadeImage($file, $directory, $width = null, $height = null)
  {
    $img = Image::read($file);
    $thumbsPath = storage_path('app/public/images/' . $directory);
    $name = time() . '_' . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();

    if (null != $width && null != $height)
      $img->resize($width, $height);

    $img->save($thumbsPath . '/' . $name);
    return (string) $name;
  }

  public function uploadeImageBase64($file, $directory, $width = null, $height = null)
  {
    if (!File::isDirectory(storage_path('app/public/images/' . $directory))) {
      File::makeDirectory(storage_path('app/public/images/' . $directory), 0777, true, true);
    }
    $img = Image::read($file);
    $thumbsPath = storage_path('app/public/images/' . $directory);
    $name = time() . '_' . rand(1111, 9999) . '.png';

    if (null != $width && null != $height)
      $img->resize($width, $height);

    $img->save($thumbsPath . '/' . $name);
    return (string) $name;
  }

  public function deleteFile($file_name, $directory = 'unknown'): void
  {
    if ($file_name && $file_name != 'default.png' && file_exists(storage_path("app/public/images/$directory/$file_name"))) {
      unlink(storage_path("app/public/images/$directory/$file_name"));
    }
  }

  public function deleteVideo($file_name, $directory = 'unknown'): void
  {
    if ($file_name && $file_name != 'default.mp4' && file_exists(storage_path("app/public/images/$directory/$file_name"))) {
      unlink(storage_path("app/public/images/$directory/$file_name"));
    }
  }
  public function defaultCover($directory)
  {
    return asset("/storage/images/$directory/cover.png");
  }

  public function defaultFile($directory)
  {
    return asset("/storage/images/$directory/default.pdf");
  }

  public function defaultImage($directory)
  {
    return asset("/storage/images/$directory/default.png");
  }

  public function defaultVideo($directory)
  {
    return asset("/storage/images/$directory/default.mp4");
  }

  public function defaultSound($directory)
  {
    return asset("/storage/images/$directory/default.mp3");
  }

  public static function getImage($name, $directory)
  {
    return asset("storage/images/$directory/" . $name);
  }

  public static function getVideo($name, $directory)
  {
    return asset("storage/images/$directory/" . $name);
  }

  public static function getSound($name, $directory)
  {
    return asset("storage/images/$directory/" . $name);
  }

  public function getFile($fileName = null, $folderName = '')
  {
    if (stripos($fileName, 'https://via.placeholder.com') !== false) {
      return $fileName;
    }

    if (empty($fileName)) {
      return $this->getDefaultFile($folderName);
    }

    $paths = [
      public_path('storage/images/' . $folderName . '/' . $fileName), 
      public_path('defaults/' . $folderName . '/' . $fileName),       
      storage_path('app/public/images/' . $folderName . '/' . $fileName), 
    ];

    foreach ($paths as $path) {
      if (File::exists($path)) {
        if (strpos($path, 'storage/app/public') !== false) {
          return asset('storage/images/' . $folderName . '/' . $fileName);
        } elseif (strpos($path, 'public/defaults') !== false) {
          return asset('defaults/' . $folderName . '/' . $fileName);
        } else {
          return asset('storage/images/' . $folderName . '/' . $fileName);
        }
      }
    }

    return $this->getDefaultFile($folderName);
  }

  private function getDefaultFile($folderName)
  {
    $defaultFiles = [
      'default.mp3',
      'default.png',
      'default.pdf',
      'default.jpg'
    ];

    foreach ($defaultFiles as $defaultFile) {
      if (File::exists(public_path('defaults/' . $folderName . '/' . $defaultFile))) {
        return asset('defaults/' . $folderName . '/' . $defaultFile);
      }
    }

    // آخر حل
    return asset('defaults/default.png');
  }

  public function saveAvatar($userName, $directory, $id)
  {
    if (!File::isDirectory(storage_path('app/public/images/' . $directory))) {
      File::makeDirectory(storage_path('app/public/images/' . $directory), 0777, true, true);
    }
    $thumbsPath = storage_path('app/public/images/' . $directory);
    $name = $userName . '.png';

    if (File::exists(storage_path('app/public/images/' . $directory . '/' . $id . '.png'))) {
      return $name;
    }
    // create avatar
    Avatar::create(
      $userName,
      config('laravolt.avatar.chars'),
      config('laravolt.avatar.uppercase'),
      config('laravolt.avatar.ascii'),
    )->save($thumbsPath . '/' . $id . '.png');

    return $name;
  }

  public function allowedImagesMimeTypes()
  {
    $extension = [
      'image/gif',
      'image/jpeg',
      'image/png',
      'image/swf',
      'image/psd',
      'image/bmp',
      'image/tiff',
      'image/tiff',
      'image/jpc',
      'image/jp2',
      'image/jpf',
      'image/jb2',
      'image/swc',
      'image/aiff',
      'image/wbmp',
      'image/xbm',
      'image/webp',
      'application/octet-stream'
    ];

    return $extension;
  }
}
