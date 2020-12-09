<?php

namespace App\Service;

use Mailjet\Client;
use Mailjet\Resources;
use Mailjet\Response;
use Symfony\Component\Mime\Email;

/**
 * Class MailjetAdministrator
 * @package App\Service
 */
class MailjetAdministrator
{
    /**
     * @var Client
     */
    private Client $mj;

    /**
     * MailjetAdministrator constructor.
     */
    public function __construct()
    {
        $this->mj=new Client($_ENV['MAILJET_PUBLIC_KEY'], $_ENV['MAILJET_PRIVATE_KEY'], true, ['version' => 'v3']);
    }


    /**
     * @param Email $mail
     * @return array
     */
    public function setBody($mail)
    {
        return [
            'Email' => $mail,
            'Action' => "addforce"
        ];
    }

    /**
     *
     * Create new Event List
     *
     * @param $name
     * @return mixed
     */
    public function createList($name)
    {
        $body = [
                'Name' => $name
            ];
        $response = $this->mj->post(Resources::$Contactslist, ['body' => $body]);
        $response->success();

        return $response->getBody()["Data"][0]["ID"];
    }

    /**
     *
     * Check if list exist
     *
     * @param $name
     * @return bool|mixed
     */
    public function alreadyExistList($name)
    {
        $filters = [
            'Limit'=>1000,  // default is 10, max is 1000
        ];
        $responses = $this->mj->get(Resources::$Contactslist, ['filters'=>$filters]);
        $responses->success();
        foreach ($responses->getBody()["Data"] as $response) {
            if ($response['Name'] == $name) {
                return $response['ID'];
            }
        }
        return false;
    }

    /**
     *
     * Add contact to event list
     *
     * @param $mail
     * @param $list
     * @return Response
     */
    public function addContact($mail, $list)
    {
        //If list not exist, create, else use existant list
        if (!$this->alreadyExistList($list)) {
            $listId=$this->createList($list);
        } else {
            $listId=$this->alreadyExistList($list);
        }
        $body=$this->setBody($mail);
        $response = $this->mj->post(Resources::$ContactslistManagecontact, ['id' => $listId, 'body' => $body]);
        $response->success();

        return $response;
    }
}
