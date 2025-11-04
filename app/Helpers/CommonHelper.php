<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class CommonHelper
{
    /**
     * Upload image and delete old one if needed.
     *
     * @param  UploadedFile|null  $file
     * @param  string|null  $oldFilePath  // old file relative to public path
     * @param  string  $folderPath        // folder inside /public/uploads/
     * @return string|null                // returns file path to store in DB
     */
    public static function uploadImage(?UploadedFile $file, ?string $oldFilePath, string $folderPath)
    {
        if (!$file) {
            return $oldFilePath; // no new file uploaded
        }

        // ✅ Delete old image if exists
        if ($oldFilePath && file_exists(public_path($oldFilePath))) {
            @unlink(public_path($oldFilePath));
        }

        // ✅ Prepare destination folder
        $destinationPath = public_path('uploads/' . trim($folderPath, '/'));
        
        // if (!file_exists($destinationPath)) {
        //     mkdir($destinationPath, 0777, true);
        // }

        // ✅ Generate a clean unique name
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = time() . '_' . $originalName . '.' . $file->getClientOriginalExtension();
        $file->move($destinationPath, $fileName);

        // ✅ Return relative path to store in DB
        return 'uploads/' . trim($folderPath, '/') . '/' . $fileName;
    }
}
