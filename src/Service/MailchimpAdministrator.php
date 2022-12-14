<?php

namespace App\Service;

use Exception;
use Symfony\Component\Mime\Email;


use \DrewM\MailChimp\MailChimp;

/**
 * Class MailchimpAdministrator
 * @package App\Service
 */
class MailchimpAdministrator
{
    /**
     * @var MailChimp
     */
    private MailChimp $mc;

    /**
     * @var int|string
     */
    private string $list_id;

    /**
     * MailchimpAdministrator constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->mc = new MailChimp($_ENV['MAILCHIMP']);
        $this->list_id = "bd2c583d03";
    }


    /**
     * @param String $mail
     * @return array
     */
    public function setBody(String $mail)
    {
        return [
            'email_address' => $mail,
            'status' => "subscribed",
            "merge_fields" => [
                'GPDR' => "Vous acceptez de recevoir les actualités de Comètes par e-mail."
            ]
        ];
    }


    /**
     * @param String $mail
     * @return array|bool|false
     */
    public function addContact(String $mail)
    {
        $body = $this->setBody($mail);

        return $this->mc->post("lists/" . $this->list_id . "/members", $body);
    }

    public function getTemplate()
    {
        dump($this->mc->get("/templates"));
        die;
    }
}
