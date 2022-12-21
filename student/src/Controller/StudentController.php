<?php

namespace Drupal\student\Controller;


use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


/**
 * Returns responses for student routes.
 */
class StudentController extends ControllerBase {

  /**
   * Builds the Download url
   */
  public function downloadurl($scode , Request $request) {

    if ($scode == 'student') {
      $filename = 'student.csv';
    } elseif ($scode == 'result') {
      $filename = 'stresult.csv';
    }

    $headers = [
      'Content-Type' => 'text/csv', // Would want a condition to check for extension and set Content-Type dynamically
      'Content-Description' => 'File Download',
      'Content-Disposition' => 'attachment; filename=' . $filename
    ];

     $uri_prefix = 'public://downloads/';
     $uri = $uri_prefix . $filename;

     return new BinaryFileResponse($uri, 200, $headers, true );

  }

}
