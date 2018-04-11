<?php
  
  namespace prosys\core\common\types;

  /**
   * Třída Zip slouží pro zipování do archivu.
   *
   * @author    Pavel Filípek <www.filipek-czech.cz>
   * @copyright © 2017, Proclient s.r.o.
   * @created   16.08.2017
   */
  class Zip
  {
    /**
     * Add files and sub-directories in a folder to zip file.
     *
     * @param string $folder
     * @param ZipArchive $zipFile
     * @param int $exclusiveLength Number of text to be exclusived from the file path.
     */
    private static function folderToZip($folder, &$zipFile) {
      /* @var $zipFile \ZipArchive */
      $handle = opendir($folder);
      
      while (false !== $f = readdir($handle)) {
        if ($f != '.' && $f != '..') {
          $filePath = $folder . '/' . $f;
          
          // Remove prefix from file path before add to zip.
          $localPath = str_replace($folder, '', $filePath);
          if (is_file($filePath)) {
            $zipFile->addFile($filePath, $localPath);
          } elseif (is_dir($filePath)) {
            // Add sub-directory.
            $zipFile->addEmptyDir($localPath);
            self::folderToZip($filePath, $zipFile);
          }
        }
      }
      closedir($handle);
    }
  
    /**
     * Zip a folder (include itself).
     * Usage:
     *   Zip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
     *
     * @param string $sourcePath Path of directory to be zip.
     * @param string $outZipPath Path of output zip file.
     */
    public static function zipDir($sourcePath, $outZipPath) {
      $pathInfo = pathInfo($sourcePath);
    
      $z = new \ZipArchive();
      $z->open($outZipPath, \ZipArchive::CREATE);
      self::folderToZip($sourcePath, $z);
      $z->close();
    }
  }