<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticMailTesterBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;

class MailTesterController extends FormController
{

    /**
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendToMailTesterAction($objectId)
    {
        $model  = $this->getModel('email');
        $entity = $model->getEntity($objectId);

        // Prepare a fake lead
        /** @var \Mautic\LeadBundle\Model\FieldModel $fieldModel */
        $fieldModel = $this->getModel('lead.field');
        $fields     = $fieldModel->getFieldList(false, false);
        array_walk(
            $fields,
            function (&$field) {
                $field = "[$field]";
            }
        );
        $fields['id'] = 0;

        $mailTesterUsername = 'mautic';

        $clientId = md5(
            $this->get('mautic.helper.user')->getUser()->getEmail().
            $this->coreParametersHelper->getParameter('site_url')
        );
        $uniqueId = $mailTesterUsername.'-'.$clientId.'-'.time();
        $email    = $uniqueId.'@mail-tester.com';

        $users = [
            [
                // Setting the id, firstname and lastname to null as this is a unknown user
                'id'        => '',
                'firstname' => '',
                'lastname'  => '',
                'email'     => $email,
            ],
        ];

        // send test email
        $model->sendSampleEmailToUser($entity, $users, $fields, [], [], false);

        // redirect to mail-tester
        return $this->postActionRedirect(
            [
                'returnUrl' => 'https://www.mail-tester.com/'.$uniqueId,
            ]
        );
    }
}
