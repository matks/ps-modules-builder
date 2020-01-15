<?php

namespace PrestaShopTests\Integration;

/**
 * @link https://github.com/sureshdotariya/folder-compare/
 */
class FolderComparator
{
    private $blacklist = [
        '.',
        '..',
        '.git',
        'autoload.php',
        'autoload_classmap.php',
        'autoload_static.php',
        'autoload_real.php',
        'ClassLoader.php',
    ];

    /**
     * @param $folderA
     * @param $folderB
     * @param $reference
     *
     * @return array list of items that differ
     */
    public function compareFolders($folderA, $folderB, $reference)
    {
        $itemsDiffer = [];
        $handle = opendir($folderA);

        while (($file = readdir($handle)) !== false) {
            if (in_array($file, $this->blacklist)) {
                continue;
            }

            $fileA = $folderA . DIRECTORY_SEPARATOR . $file;
            $fileB = $folderB . DIRECTORY_SEPARATOR . $file;
            $fullPath = $reference . DIRECTORY_SEPARATOR . $file;

            if (is_file($fileA)) {

                if (!file_exists($fileB)) {
                    $itemsDiffer[] = $fullPath . ' is missing';
                } else {
                    if (is_file($fileB)) {
                        if (md5_file($fileA) !== md5_file($fileB)) {
                            $itemsDiffer[] = $fullPath . ' has different md5';
                        }
                    } elseif (is_dir($fileB)) {
                        $itemsDiffer[] = $fullPath . ' is once a dir, once a file';
                    }
                }
            } else {
                $itemsDiffer = array_merge(
                    $itemsDiffer,
                    $this->compareFolders($fileA, $fileB, $fullPath)
                );
            }
        }

        closedir($handle);

        return $itemsDiffer;
    }
}
