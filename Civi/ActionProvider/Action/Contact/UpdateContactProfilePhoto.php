<?php
/**
 * Copyright (C) 2023  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\ExecutionException;
use Civi\ActionProvider\Parameter\FileSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;
use CRM_Core_Config;
use CRM_Core_Exception;
use CRM_Utils_File;
use CRM_Utils_System;

class UpdateContactProfilePhoto extends AbstractAction {

  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output): void {
    $config = CRM_Core_Config::singleton();
    $uploadLocation = $config->customFileUploadDir . DIRECTORY_SEPARATOR;


    try {
      $contact = civicrm_api3('Contact', 'getsingle', ['id' => $parameters->getParameter('contact_id')]);
      if (!empty($contact['image_URL'])) {
        $matches = [];
        preg_match_all('/^.*civicrm\/contact\/imagefile[\?&]photo=(.*)$/m', $contact['image_URL'], $matches, PREG_SET_ORDER);
        if (isset($matches[0][1])) {
          if (file_exists($uploadLocation . $matches[0][1])) {
            unlink($uploadLocation.$matches[0][1]);
          }
        }
      }
    }
    catch (CRM_Core_Exception $e) {
      // Do nothing
    }

    $contactId = $parameters->getParameter('contact_id');
    $apiParams['id'] = $contactId;
    $apiParams['image_URL'] = 'null';
    if ($parameters->doesParameterExists('file')) {
      $file = $parameters->getParameter('file');
      if (!empty($file)) {
        $fileContent = '';
        if (isset($file['content'])) {
          $fileContent = base64_decode($file['content']);
        }
        elseif (isset($file['url'])) {
          $fileContent = file_get_contents($file['url']);
        }
        $extension = explode('/', $file['mime_type'] )[1];
        $fileName = 'contact_'.$contactId.'.'.$extension;
        $fileName = CRM_Utils_File::makeFileName($uploadLocation . $fileName) ;
        file_put_contents($uploadLocation . $fileName, $fileContent);
        $apiParams['image_URL'] =  CRM_Utils_System::url('civicrm/contact/imagefile', 'photo=' . $fileName, FALSE, NULL, TRUE, TRUE);
      }
    }
    try {
      civicrm_api3('Contact', 'create', $apiParams);
    }
    catch (CRM_Core_Exception $e) {
      throw new ExecutionException($e->getMessage(), $e->getCode(), $e);
    }
  }

  public function getConfigurationSpecification(): SpecificationBag {
    return new SpecificationBag([]);
  }

  public function getParameterSpecification(): SpecificationBag {
    return new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
      new FileSpecification('file', E::ts('File'), false),
    ]);
  }

}
