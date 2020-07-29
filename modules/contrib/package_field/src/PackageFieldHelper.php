<?php

namespace Drupal\package_field;

use Drupal\Core\File\FileSystem;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Utility\UrlHelper;
use Drupal\file\Entity\File;
use Drupal\Core\StreamWrapper\PublicStream;
use DOMDocument;

/**
 * Helper service for package_field.
 */
class PackageFieldHelper {
  use StringTranslationTrait;

  /**
   * File system services.
   *
   * @var Drupal\Core\File\FileSystem
   */
  protected $fileSystem;
  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Archiver class.
   *
   * @var mixed
   */
  private $archiver;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    FileSystem $fileSystem,
    MessengerInterface $messenger) {
    $this->fileSystem = $fileSystem;
    $this->messenger = $messenger;
  }

  /**
   * Get an appropriate archiver class for the file.
   *
   * @param string $file
   *   The file path.
   */
  public function getArchiver($file) {
    $extension = strstr(pathinfo($file)['basename'], '.');
    switch ($extension) {
      case '.tar.gz':
      case '.tar':
        $this->archiver = new \PharData($file);
        break;

      case '.zip':
        $this->archiver = new \ZipArchive($file);
        $this->archiver->open($file);
      default:
        break;
    }
    return $this->archiver;
  }

  /**
   * Php delete function that deals with directories recursively.
   */
  public function deleteFiles($target) {

    if (is_dir($target)) {
      $files = glob("$target" . '{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE);
      foreach ($files as $file) {
        $this->deleteFiles($file);
      }
      rmdir($target);
    }
    elseif (is_file($target)) {
      unlink($target);
    }
  }

  /**
  * Build full file path from URI.
  */
  function buildPathFromUri($uri) {
    $wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);

    return $wrapper->realpath();
  }

  /**
  * Build destination path from $file.
  */
  function buildDestination(File $file, $base_uri) {
    $file_path = $this->buildPathFromUri($base_uri);

    return $file_path . '/' . $file->id();
  }

}
