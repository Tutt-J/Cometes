<?php

namespace App\Service;

use App\Entity\Opinion;
use App\Entity\Purchase;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Instagram\Api;
use Instagram\Exception\InstagramAuthException;
use Instagram\Exception\InstagramException;
use Instagram\Exception\InstagramFetchException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

/**
 * Class SocialGenerator
 * @package App\Service
 */
class GlobalsGenerator
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * BasketAdministrator constructor.
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->logger=$logger;
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function getLastInstagramPost()
    {
        $json=$this->sendCurl("https://graph.instagram.com/me/media?fields=media_url,permalink&access_token=".$_ENV['INSTAGRAM_TOKEN']);

        if($json){
            return $json->data;
        }
    }

    public function sendCurl($url){
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36');
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $html = curl_exec($curl);
        curl_close($curl);
        return json_decode($html);
    }

    /**
     * @return object[]
     */
    public function getOpinions($type)
    {
        $type = $this->em
            ->getRepository(Type::class)
            ->findBy(
                    [
                       'slug' => $type
                    ]
            );
        return $this->em
            ->getRepository(Opinion::class)
            ->findBy(
                [
                    'isOnline' => 1,
                    'type' => $type
                ],
                ['id' => 'DESC']
            );
    }

    public function getAmountMonth($month){
        $result=$this->em
            ->getRepository(Purchase::class)
            ->findByMonth($month);
        return $result[0]['amount'];
    }
}
